<?php
/**
* 2007-2019 PrestaShop
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
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/


if (!defined('_PS_VERSION_')) {
    exit;
}

class Feature_groups extends Module
{
    protected $config_form = false;

    public function __construct()
    {

        $this->name = 'feature_groups';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Sergio';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('feature_groups');
        $this->description = $this->l('A module to create groups of features');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        $this->selected_language_id = ( !$this->isEmpty( Tools::getValue('FEATURE_GROUPS_LANG') ))
        									? (int)Tools::getValue('FEATURE_GROUPS_LANG')
        									: $this->context->language->id;

        $this->selected_feature_id = ( !$this->isEmpty( Tools::getValue('FEATURE_GROUPS_FEATURE') ))
                                            ? (int)Tools::getValue('FEATURE_GROUPS_FEATURE')
                                            : null;
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        include_once("sql/install.php");

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayFeatureForm') &&
            $this->registerHook('actionFeatureDelete') &&
            $this->registerHook('displayFooterProduct');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submit'.$this->name)) == true)
        {
             if (((Tools::getValue('FEATURE_GROUPS_LANG') != false && (int)Tools::getValue('FEATURE_GROUPS_LANG') == $this->selected_language_id)
                    && (Tools::getValue('FEATURE_GROUPS_FEATURE') != false && (int)Tools::getValue('FEATURE_GROUPS_FEATURE') == $this->selected_feature_id)
                    &&  $this->isEmpty(Tools::getValue('ignore_changes'))) )
             {
                $this->postProcess();
             }
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();

    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = true;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit'.$this->name;
               $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value['FEATURE_GROUPS_LANG'] = null;
        $helper->fields_value['ignore_changes'] = null;
        $helper->fields_value['FEATURE_GROUPS_FEATURE'] = null;

        $helper->fields_value['FEATURE_GROUPS_GROUP'] =
                            FEATURE_GROUPSModel::getContent(Tools::getValue('FEATURE_GROUPS_FEATURE'), Tools::getValue('FEATURE_GROUPS_LANG'));

        $helper->fields_value['FEATURE_GROUPS_FEATURE_IMAGE'] = basename(FEATURE_GROUPSModel::getImage(Tools::getValue('FEATURE_GROUPS_FEATURE')));

        $helper->fields_value['FEATURE_GROUPS_UNASSIGNED_GROUPS'] =
                            Configuration::get('feature_groups_unnassigned_groups');

        return $helper->generateForm($this->getConfigForm());
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $languages_list = Language::getLanguages(false);
        $languages_array= array();
        foreach ($languages_list as $lang){
            $languages_array[] = array( 'id_lang' => $lang['id_lang'], 'name' => $lang['name'] );
        }

        $feature_list = Feature::getFeatures($this->selected_language_id, false);
        $features_array= array();
        foreach ($feature_list as $feature) {
            $features_array[] = array('id_feature' => $feature['id_feature'], 'name_feature' => $feature['name'],
                                      'name_id_feature' => $feature['id_feature']." - ".$feature['name']);
        }

        $form_fields[]['form'] =  array(
                                  'legend' => array(
                                        'title' => $this->l('Language'),
                                        'icon' => 'icon-cogs',
                                  ),
                                  'input' => array(
                                                  array(
                                                      'type' => 'select',
                                                      'name' => 'FEATURE_GROUPS_LANG',
                                                      'label' => $this->l('Language'),
                                                      'desc' => $this->l('Choose a language'),
                                                      'options' => array(
                                                          'query' => $languages_list,
                                                          'id' => 'id_lang',
                                                          'name' => 'name'
                                                      )
                                                  ),
                                                  array(
                                                      'type' => 'hidden',
                                                      'name' => 'ignore_changes',
                                                      'id' => 'ignore_changes',
                                                  )
                                  )
                              );
        $form_fields[]['form'] = array(
                                  'legend' => array(
                                                    'title' => $this->l('Feature & Group'),
                                                    'icon' => 'icon-cogs',
                                                    ),
                                  'input' => array(
                                                  array(
                                                      'type' => 'select',
                                                      'name' => 'FEATURE_GROUPS_FEATURE',
                                                      'label' => $this->l('Feature'),
                                                      'desc' => $this->l('Choose a feature'),
                                                      'options' => array(
                                                          'query' => $features_array,
                                                          'id' => 'id_feature',
                                                          'name' => 'name_id_feature'
                                                      )
                                                  ),
                                                  array(
                                                      'col' => 3,
                                                      'type' => 'text',
                                                      'name' => 'FEATURE_GROUPS_GROUP',
                                                      'prefix' => '<i class="icon icon-sitemap"></i>',
                                                      'label' => $this->l('Group'),
                                                      'desc' => $this->l('Choose a name for the group')
                                                  ),
                                                  array(
                                                      'name' => 'FEATURE_GROUPS_FEATURE_IMAGE',
                                                      'type' => 'file',
                                                      'label' => $this->l("Feature's Image"),
                                                      'path' => $this->_path,
                                                      'imagesExtensions' => array( 'jpg','gif','png' ),
                                                      'display_image' => true
                                                  ),
                                              ),
                                  'submit' => array(
                                                    'title' => $this->l('Save'),
                                              ),
                              );
        $form_fields[]['form'] = array(
                                  'legend' => array(
                                                    'title' => $this->l('Unassigned Group'),
                                                    'icon' => 'icon-cogs',
                                                    ),
                                  'input' => array(
                                                array(
                                                    'col' => 3,
                                                    'type' => 'text',
                                                    'name' => 'FEATURE_GROUPS_UNASSIGNED_GROUPS',
                                                    'label' => $this->l('Unassigned Group'),
                                                    'desc' => $this->l('Choose a name for the unassigned group')
                                                ),
                                              ),
                                  'submit' => array(
                                                    'title' => $this->l('Save'),
                                              ),
                              );

        return $form_fields;
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
         if (Tools::getValue('FEATURE_GROUPS_FEATURE') !== false){
            FEATURE_GROUPSModel::setContent(Tools::getValue('FEATURE_GROUPS_FEATURE'));

            if(isset($_FILES['FEATURE_GROUPS_FEATURE_IMAGE']) && $_FILES['FEATURE_GROUPS_FEATURE_IMAGE']["error"] == UPLOAD_ERR_OK){
                $uploadDir = dirname(dirname(_PS_TMP_IMG_DIR_))._THEME_PROD_PIC_DIR_.$this->name;
                $uploadDir = str_replace('\\', '/', $uploadDir);
                $pathToUse = "/".basename(dirname($uploadDir))."/".basename($uploadDir);
                if (file_exists($uploadDir)){
                    $tmp_name = $_FILES["FEATURE_GROUPS_FEATURE_IMAGE"]["tmp_name"];
                    $name = $_FILES["FEATURE_GROUPS_FEATURE_IMAGE"]["name"];
                    move_uploaded_file($tmp_name, "$uploadDir/$name");
                }
                FEATURE_GROUPSModel::setImage(Tools::getValue('FEATURE_GROUPS_FEATURE'), "$pathToUse/$name");
            }

            FEATURE_GROUPSModel::setLangContent(FEATURE_GROUPSModel::getGroup(
                    Tools::getValue('FEATURE_GROUPS_FEATURE')), Tools::getValue('FEATURE_GROUPS_LANG'), Tools::getValue('FEATURE_GROUPS_GROUP'));

            if(Tools::getValue('FEATURE_GROUPS_UNASSIGNED_GROUPS') !== false){
                Configuration::updateValue('feature_groups_unnassigned_groups',Tools::getValue('FEATURE_GROUPS_UNASSIGNED_GROUPS'));
            }
         }
    }

    public function hookHeader($params){
       // die(var_dump($this->context->controller->addCSS));
        $this->context->controller->addJS($this->_path.'views/js/front.js');
        $this->context->controller->addCSS($this->_path.'views/css/front.css', 'all');
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (strcmp(Tools::getValue('configure'), $this->name) === 0) {
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */

    public function hookDisplayFeatureForm()
    {
        /* Place your code here. */
        $this->context->controller->addJS($this->_path.'/views/js/display.js');
        $this->context->controller->addCSS($this->_path.'/views/css/display.css');
    }

     public function hookDisplayFooterProduct($params)
        {
            $unassignedGroupGroup = Configuration::get('feature_groups_unnassigned_groups');
            $groups= array();
            $featuresGrouped = array();
            $features = $params['product']->features;
            foreach($features as $feature){
            $feature_group = FEATURE_GROUPSModel::getContent($feature['id_feature'], $this->context->language->id);

                $featuresGrouped[] = array('group_id' => FEATURE_GROUPSModel::getGroup($feature['id_feature']),
                                           'name_group' => $feature_group,
                                           'id_feature' => $feature['id_feature'],
                                           'name_feature' => $feature['name'],
                                           'value_feature' => $feature['value'],
                                           'feature_image' => FEATURE_GROUPSModel::getImage($feature['id_feature'])
                                     );

                if(!in_array(array('name_group' => $feature_group), $groups)){
                    $groups[]= array('name_group' => $feature_group);
                }
            }

            $this->context->smarty->assign(array('features_group' => $featuresGrouped));
            $this->context->smarty->assign('groups', $groups);
            $this->context->smarty->assign('unassignedGroup', $unassignedGroupGroup);
            $output = $this->context->smarty->fetch($this->local_path.'views/templates/front/feature_groups.tpl');

            return $output;
        }

     public function hookActionFeatureDelete($params)
     {
        FEATURE_GROUPSModel::onFeatureDelete(Tools::getValue('id_feature'));
     }

     private function isEmpty($value)
     {
        return empty($value) ? true : false;
     }
}

class FEATURE_GROUPSModel extends ObjectModel{

    public static $module_name = 'feature_groups';
    public static $lang_table = 'feature_groups_lang';

    public static function setContent($feature)
    {
        $feature = (int)$feature;
        $sql = 'INSERT IGNORE INTO `'._DB_PREFIX_.self::$module_name.'` (`id_feature`)
                  VALUES ("'.$feature.'")
                ';

        return Db::getInstance()->execute( $sql );
    }

    public static function setLangContent($groupID, $id_lang, $group)
    {
        $groupID = (int)$groupID;
        $id_lang = (int)$id_lang;
        $group = pSQL( $group, true );
        $sql = 'INSERT INTO `'._DB_PREFIX_.self::$lang_table.'` (`id_group`, `id_lang`, `name_group`)
                    VALUES ("'.$groupID.'", "'.$id_lang.'", "'.$group.'")
                    ON DUPLICATE KEY UPDATE
                    name_group = "'.$group.'"
                ';

        return Db::getInstance()->execute( $sql );
    }

    public static function getContent($feature, $id_lang)
    {
        $feature = (int)$feature;
        $id_lang = (int)$id_lang;
        $sql = 'SELECT `'._DB_PREFIX_.self::$lang_table.'`.`name_group` FROM `'._DB_PREFIX_.self::$lang_table.'`
                INNER JOIN `'._DB_PREFIX_.self::$module_name.'`
                ON `'._DB_PREFIX_.self::$module_name.'`.`id_group` = `'._DB_PREFIX_.self::$lang_table.'`.`id_group`
                WHERE `'._DB_PREFIX_.self::$module_name.'`.`id_feature` = "'.$feature.'"
                AND `'._DB_PREFIX_.self::$lang_table.'`.`id_lang` = "'.$id_lang.'"
                ';

        return Db::getInstance()->getValue($sql);
    }

    public static function getGroup($feature)
    {
        $feature = (int)$feature;
        $sql = 'SELECT `id_group` FROM `'._DB_PREFIX_.self::$module_name.'` WHERE `id_feature` = "'.$feature.'"
                ';

        return Db::getInstance()->getValue( $sql );
    }

    public static function onFeatureDelete($feature)
    {
        $feature = (int)$feature;
        $sql = 'DELETE `'._DB_PREFIX_.self::$module_name.'`, `'._DB_PREFIX_.self::$lang_table.'` FROM `'._DB_PREFIX_.self::$module_name.'`
                INNER JOIN `'._DB_PREFIX_.self::$lang_table.'`
                ON `'._DB_PREFIX_.self::$module_name.'`.`id_group` = `'._DB_PREFIX_.self::$lang_table.'`.`id_group`
                WHERE `'._DB_PREFIX_.self::$module_name.'`.`id_feature` = "'.$feature.'"
                ';

        return Db::getInstance()->execute( $sql );
    }

    public static function setImage($feature, $image)
    {
        $feature = (int)$feature;
        $image = pSQL( $image, true );
        $sql = 'INSERT INTO `'._DB_PREFIX_.self::$module_name.'` (`id_feature`, `feature_image`)
                    VALUES ("'.$feature.'", "'.$image.'")
                    ON DUPLICATE KEY UPDATE
                    `feature_image` = "'.$image.'"
                ';

        return Db::getInstance()->execute( $sql );
    }

    public static function getImage($feature)
    {
        $feature = (int)$feature;
        $sql = 'SELECT `feature_image` FROM `'._DB_PREFIX_.self::$module_name.'` WHERE `id_feature` = "'.$feature.'"
                ';

        return Db::getInstance()->getValue( $sql );
    }

}