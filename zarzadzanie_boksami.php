<?php

/**
 * 2007-2023 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2023 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Zarzadzanie_boksami extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'zarzadzanie_boksami';
        $this->tab = 'advertising_marketing';
        $this->version = '2.0.0';
        $this->author = 'Bartosz Walczak';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Zarzadzanie_boksami');
        $this->description = $this->l('Zarzadzanie boksami');
    }

    public function install()
    {
        include(dirname(__FILE__) . '/sql/install.php');

        return parent::install() &&
            $this->registerHook('displayTopColumn') &&
            $this->registerHook('displayHeaderCategory') &&
            $this->registerHook('displayHome') &&
            $this->registerHook('displayCMSDisputeInformation') &&
            $this->registerHook('displayProductAdditionalInfo') &&
            $this->registerHook('header') &&
            $this->registerHook('displayBackOfficeHeader');
    }

    public function uninstall()
    {
        include(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall() &&
            $this->unregisterHook('displayTopColumn') &&
            $this->unregisterHook('displayHeaderCategory') &&
            $this->unregisterHook('displayHome') &&
            $this->unregisterHook('displayCMSDisputeInformation') &&
            $this->unregisterHook('displayProductAdditionalInfo') &&
            $this->unregisterHook('header') &&
            $this->unregisterHook('displayBackOfficeHeader');
    }

    protected function prepareCategoryTree($categories)
    {
        $categoryMap = array_column($categories, null, 'id_category');
        $categoryTree = [];
        foreach ($categories as $category) {
            if ($category['id_category'] == 2) {
                $categoryTree[] = $this->buildCategoryTree($category, $categoryMap);
            }
        }
        return $categoryTree;
    }

    protected function buildCategoryTree($category, $categoryMap)
    {
        $categoryTree = [
            'id_category' => $category['id_category'],
            'name' => $category['name'],
            'subcategories' => [],
        ];

        if (isset($categoryMap[$category['id_category']])) {
            foreach ($categoryMap as $childCategory) {
                if ($childCategory['id_parent'] == $category['id_category']) {
                    $categoryTree['subcategories'][] = $this->buildCategoryTree($childCategory, $categoryMap);
                }
            }
        }
        return $categoryTree;
    }

    protected function generateCategoryTreeHTML($categoryTree, $category_id = null)
    {
        $html = '<ul style="list-style-type: none;">';
        foreach ($categoryTree as $category) {
            $html .= '<li>';
            $html .= '<label>';
            $html .= '<li><input  type="radio" name="boks_category_page_id"  value="' . $category['id_category'] . '"';
            if ($category_id == $category['id_category']) {
                $html .= ' checked="checked"';
            }
            $html .= '>';
            $html .= $category['name'];
            $html .= '</label>';
            if (!empty($category['subcategories'])) {
                $html .= $this->generateCategoryTreeHTML($category['subcategories'], $category_id);
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    private function assignFormTemplateVariables($prevCategory = null)
    {
        $languageId = Context::getContext()->language->id;
        $products = Product::getProducts($languageId, 0, 0, 'id_product', 'ASC', false, false, null);
        $categories = Category::getCategories(Context::getContext()->language->id, false, false);
        $categoryTree = $this->prepareCategoryTree($categories);
        $categoryTreeTemplate = $this->generateCategoryTreeHTML($categoryTree, $prevCategory);
        $staticPages = CMS::getCMSPages(Context::getContext()->language->id);

        $this->context->smarty->assign([
            'staticPages' => $staticPages,
            'categoryTree' => $categoryTreeTemplate,
            'products' => $products,
        ]);
    }

    public function getContent()
    {
        $submitAddNew = Tools::isSubmit('add_new');
        $submitDelete = Tools::isSubmit('delete');
        $submitEdit = Tools::isSubmit('edit');

        if ($submitAddNew) {
            $this->assignFormTemplateVariables();
            if (Tools::isSubmit('submit')) {
                $this->handleQueries('addNew');
            }
            $this->context->smarty->assign([]);
            return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/helpers/form/form.tpl');
        }

        if ($submitDelete) {
            $this->handleQueries('delete');
        }

        if ($submitEdit) {
            if (Tools::isSubmit('submit')) {
                $this->handleQueries('update');
            }

            $boks = $this->handleQueries('edit');
            $this->assignFormTemplateVariables($boks['category_page_id']);

            $this->context->smarty->assign([
                'boks' => $boks,
            ]);

            return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/helpers/form/form.tpl');
        }

        $boksy = $this->handleQueries('getAllBoksy');
        $this->context->smarty->assign([
            'boksy' => $boksy,
            'adminLink' => $this->context->link->getAdminLink('AdminModules'),

        ]);

        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
    }


    private function validateFormData()
    {
        $hasError = false;
        $boksId = (int)Tools::getValue('boks_id');
        $boksName = Tools::getValue('boks_name');
        $boksTitle = Tools::getValue('boks_title');
        $boksLink = Tools::getValue('boks_link');
        $boksStaticPage = (int)Tools::getValue('boks_static_page');
        $boksCategoryPage = (int)Tools::getValue('boks_category_page_id');
        $boksProductPage = (int)Tools::getValue('boks_product_page');
        $image_path = $this->validateBoksImage($_FILES['boks_image']);
        $prevImage = Tools::getValue('imageFromDb');

        if (empty($boksTitle) && empty($image_path)) {
            $this->context->controller->errors[] = $this->l('Tytuł lub zdjęcie jest wymagane.');
            $hasError = true;
        } elseif (is_array($image_path) && array_key_exists('error', $image_path)) {
            $this->context->controller->errors[] = $image_path['error'];
            $hasError = true;
        }

        if (empty($image_path)) {
            $image_path = $prevImage;
        }else{
            //delete old image
            $this->manageBoksImage('delete', $boksId);
        }

        if (empty($boksLink)) {
            $this->context->controller->errors[] = $this->l('Link do strony jest wymagany.');
            $hasError = true;
        }
        if (empty($boksName)) {
            $this->context->controller->errors[] = $this->l('Nazwa boksa jest wymagane.');
            $hasError = true;
        }

        if (empty($boksStaticPage)) {
            $this->context->controller->errors[] = $this->l('Pole "Strona statyczna" jest wymagane.');
            $hasError = true;
        }
        if (empty($boksCategoryPage)) {
            $this->context->controller->errors[] = $this->l('Wybierz stronę kategorii strony.');
            $hasError = true;
        }
        if (empty($boksProductPage)) {
            $this->context->controller->errors[] = $this->l('Pole "Strona produktu" jest wymagane.');
            $hasError = true;
        }

        if ($hasError) {
            return false;
        }

        return array(
            'boks_name' => $boksName,
            'boks_title' => $boksTitle,
            'boks_link' => $boksLink,
            'image_path' => $image_path,
            'boks_static_page' => $boksStaticPage,
            'boks_category_page_id' => $boksCategoryPage,
            'boks_product_page' => $boksProductPage,
        );
    }

    private function handleQueries($type)
    {
        switch ($type) {
            case 'addNew':
                $this->handleAddNew();
                break;
            case 'delete':
                $this->handleDelete();
                break;
            case 'edit':
                return $this->handleEdit();
            case 'update':
                $this->handleUpdate();
                break;
            case 'getAllBoksy':
                return $this->handleGetAllBoksy();
            default:
                $this->context->controller->errors[] = $this->l('Nieprawidłowy typ operacji.');
                break;
        }
    }

    private function handleAddNew()
    {
        $validatedData = $this->validateFormData();
        if (!$validatedData) {
            return;
        }
        $this->manageBoksImage('upload');
        $query = 'INSERT INTO `' . _DB_PREFIX_ . 'zarzadzanie_boksami` 
        (`name`, `title`, `link`, `image_path`, `static_page_id`, `category_page_id`, `product_page_id`)
        VALUES
        ("' . $validatedData['boks_name'] . '", "' . $validatedData['boks_title'] . '", "' . $validatedData['boks_link'] . '", "' . $validatedData['image_path'] . '", ' . $validatedData['boks_static_page'] . ', ' . (int)$validatedData['boks_category_page_id'] . ', ' . (int)$validatedData['boks_product_page'] . ')';

        $this->executeQueryAndRedirect($query, 'Dodano nowy boks.');
    }

    private function handleDelete()
    {
        $boksId = (int)Tools::getValue('delete');

        if ($boksId > 0) {
            $query = 'DELETE FROM `' . _DB_PREFIX_ . 'zarzadzanie_boksami` WHERE `id` = ' . $boksId;
            $this->manageBoksImage('delete', $boksId);
            $this->executeQueryAndRedirect($query);
        } else {
            $this->context->controller->errors[] = $this->l('Nieprawidłowe ID boksa.');
        }
    }

    private function handleEdit()
    {
        $boksId = (int)Tools::getValue('edit');

        if ($boksId > 0) {
            $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'zarzadzanie_boksami` WHERE `id` = ' . $boksId;
            $results = Db::getInstance()->getRow($query);
            if ($results) {
                return $results;
            } else {
                $this->context->controller->errors[] = $this->l('Błąd podczas pobierania danych boksu do edycji.');
            }
        } else {
            $this->context->controller->errors[] = $this->l('Nieprawidłowe ID boksa do edycji.');
        }
    }

    private function handleUpdate()
    {
        $validatedData = $this->validateFormData();
        if (!$validatedData) {
            return;
        }
        $boksId = (int)Tools::getValue('boks_id');
        $this->manageBoksImage('upload');

        if ($boksId > 0) {
            $query = 'UPDATE `' . _DB_PREFIX_ . 'zarzadzanie_boksami` SET
            `name` = "' . $validatedData['boks_name'] . '",
            `title` = "' . $validatedData['boks_title'] . '",
            `link` = "' . $validatedData['boks_link'] . '",
            `image_path` = "' . $validatedData['image_path'] . '",
            `static_page_id` = ' . (int)$validatedData['boks_static_page'] . ',
            `category_page_id` = ' . (int)$validatedData['boks_category_page_id'] . ',
            `product_page_id` = ' . $validatedData['boks_product_page'] . '
            WHERE `id` = ' . $boksId;
            $this->executeQueryAndRedirect($query, 'Zaktualizowano boks.');
        } else {
            $this->context->controller->errors[] = $this->l('Nieprawidłowe ID boksa do edycji.');
        }
    }

    private function handleGetAllBoksy()
    {
        $query = 'SELECT * FROM ' . _DB_PREFIX_ . 'zarzadzanie_boksami';
        $results = Db::getInstance()->executeS($query);

        if ($results) {
            return $results;
        } elseif (empty($results)) {
            $this->context->controller->informations[] = $this->l('Brak rekordów w bazie danych.');
        } else {
            $this->context->controller->errors[] = $this->l('Błąd zapytania do bazy.');
        }
    }

    private function executeQueryAndRedirect($query, $successMessage = null)
    {
        if (Db::getInstance()->execute($query)) {
            if ($successMessage) {
                $this->context->controller->confirmations[] = $successMessage;
            }
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules') . '&configure=zarzadzanie_boksami');
        } else {
            $this->context->controller->errors[] = $this->l('Błąd podczas wykonywania zapytania.');
        }
    }
    public function validateBoksImage($file)
    {
        $allowedFormats = array('jpg', 'jpeg', 'png');

        if ($file['error'] == 0) {
            $fileName = pathinfo($file['name']);
            $fileFormat = strtolower($fileName['extension']);

            if (in_array($fileFormat, $allowedFormats)) {
                $boksImagePath = _PS_BASE_URL_ . __PS_BASE_URI__ . '/modules/' . $this->name . '/uploads/' . basename($file['name']);
                return $boksImagePath;
            } else {
                return ['error' => 'Niepoprawny format. Dozwolone: JPG, JPEG, PNG.'];
            }
        }

        return null;
    }

    public function manageBoksImage($action, $boksId = null)
    {
        if ($action === 'upload' && isset($_FILES['boks_image'])) {
            $file = $_FILES['boks_image'];
            $uploadDir = _PS_MODULE_DIR_ . $this->name . '/uploads/';
            $validationResult = $this->validateBoksImage($file);

            if (is_array($validationResult) && isset($validationResult['error'])) {
                return $validationResult;
            }

            $uploadFile = $uploadDir . basename($file['name']);
            if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
                $boksImagePath = _PS_BASE_URL_ . __PS_BASE_URI__ . '/modules/' . $this->name . '/uploads/' . basename($file['name']);
                $this->context->controller->confirmations[] = $this->l('Wgrano zdjęcie.');
            } else {
                return ['error' => 'Błąd pobierania zdjęcia.'];
            }
        } elseif ($action === 'delete') {
            $image = Db::getInstance()->getRow('SELECT `image_path` FROM `' . _DB_PREFIX_ . 'zarzadzanie_boksami` WHERE `id` = ' . $boksId);
            $isImageInUse = Db::getInstance()->getValue('SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'zarzadzanie_boksami` WHERE `image_path` = \'' . pSQL($image['image_path']) . '\' AND `id` != ' . $boksId);

            if ($isImageInUse > 0) {
                $this->context->controller->errors[] = $this->l('Nie można usunąć zdjęcia, ponieważ jest używane przez inny boks.');
            } else {
                if ($boksId) {
                    $filePath = _PS_MODULE_DIR_ . $this->name . '/uploads/' . basename($image['image_path']);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                        $this->context->controller->confirmations[] = $this->l('Usunięto plik.');
                    } else {
                        $this->context->controller->errors[] = $this->l('Nie udało się odnaleźć pliku do usunięcia.');
                    }
                } else {
                    $this->context->controller->errors[] = $this->l('Nie udało się odnaleźć zdjęcia w bazie danych.');
                }
            }
        }
        return $boksImagePath;
    }


    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */

    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    // ######## PRODUCT ######## 

    public function hookDisplayProductAdditionalInfo()
    {
        $boksy = $this->handleQueries('getAllBoksy');


        $this->context->smarty->assign(
            array(
                'boksy' => $boksy,
                'page_type' => "product",
            )
        );
        return $this->fetch("module:zarzadzanie_boksami/views/templates/hook/boks.tpl");
    }


    // ######## CATEGORY ######## 

    public function hookDisplayHeaderCategory()
    {
        $boksy = $this->handleQueries('getAllBoksy');

        $this->context->smarty->assign(
            array(
                'boksy' => $boksy,
                'page_type' => "category",
            )
        );
        return $this->fetch("module:zarzadzanie_boksami/views/templates/hook/boks.tpl");
    }

    // ######## STATIC ######## 


    public function hookDisplayCMSDisputeInformation()
    {
        $boksy = $this->handleQueries('getAllBoksy');

        $this->context->smarty->assign(
            array(
                'boksy' => $boksy,
                'page_type' => "static",
            )
        );
        return $this->fetch("module:zarzadzanie_boksami/views/templates/hook/boks.tpl");
    }

    // ######## HOME ######## 


    public function hookDisplayHome()
    {
        $boksy = $this->handleQueries('getAllBoksy');
        $this->context->smarty->assign(
            array(
                'boksy' => $boksy,
                'page_type' => "homePage",
            )
        );
        return $this->fetch("module:zarzadzanie_boksami/views/templates/hook/boks.tpl");
    }
}
