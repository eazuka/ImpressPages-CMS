<?php
/**
 * @package   ImpressPages
 * @copyright Copyright (C) 2011 ImpressPages LTD.
 * @license   GNU/GPL, see ip_license.html
 */
namespace Modules\standard\languages;

if (!defined('BACKEND')) exit;

require_once(BASE_DIR.MODULE_DIR.'developer/std_mod/std_mod.php');
require_once(__DIR__.'/db.php');
require_once(__DIR__.'/element_url.php');


class LanguageArea extends \Modules\developer\std_mod\Area {

    var $errors = array();
    private $urlBeforeUpdate;

    function __construct() {
        global $parametersMod;
        parent::__construct(
        array(
            'dbTable' => 'language',
            'title' => $parametersMod->getValue('standard','languages','admin_translations','languages'),
            'dbPrimaryKey' => 'id',
            'searchable' => false,
            'orderBy' => 'row_number',
            'sortable' => true,
            'sortField' => 'row_number',
            'newRecordPosition' => 'bottom'
            )
            );



            $element = new \Modules\developer\std_mod\ElementText(
            array(
                    'title' => $parametersMod->getValue('standard','languages','admin_translations','short'),
                    'showOnList' => true,
                    'dbField' => 'd_short',
                    'required' => true
            )
            );
            $this->addElement($element);


            $element = new \Modules\developer\std_mod\ElementText(
            array(
                    'title' => $parametersMod->getValue('standard','languages','admin_translations','long'),
                    'useInBreadcrumb' => true,
                    'showOnList' => true,
                    'dbField' => 'd_long',
            )
            );
            $this->addElement($element);

            $element = new \Modules\developer\std_mod\ElementBool(
            array(
                    'title' => $parametersMod->getValue('standard','languages','admin_translations','visible'),
                    'showOnList' => true,
                    'dbField' => 'visible',
            )
            );
            $this->addElement($element);





            $element = new ElementUrl(
            array(
                    'title' => $parametersMod->getValue('standard','languages','admin_translations','url'),
                    'showOnList' => true,
                    'dbField' => 'url',
                    'required' => true,
                    'regExpression' => '/^([^\/\\\])+$/',
                    'regExpressionError' => $parametersMod->getValue('standard','languages','admin_translations','error_incorrect_url')
            )
            );
            $this->addElement($element);



            $element = new \Modules\developer\std_mod\ElementText(
            array(
                    'title' => $parametersMod->getValue('standard','languages','admin_translations','code'),
                    'showOnList' => true,
                    'dbField' => 'code',
                    'required' => true
            )
            );
            $this->addElement($element);



            $element = new \Modules\developer\std_mod\ElementText(
            array(
                    'title' => $parametersMod->getValue('standard','languages','admin_translations','text_direction'),
                    'showOnList' => true,
                    'dbField' => 'text_direction',
                    'required' => true,
                    'defaultValue' => 'ltr'
            )
            );
            $this->addElement($element);


    }


    function afterInsert($id) {
        global $site;

        Db::createRootZoneElement($id);
        Db::createEmptyTranslations($id,'par_lang');

        $site->dispatchEvent('standard', 'languages', 'language_created', array('language_id'=>$id));
    }

    function beforeDelete($id) {
        global $site;



        $site->dispatchEvent('standard', 'languages', 'before_delete', array('language_id'=>$id));
    }


    function afterDelete($id) {
        global $site;


        Db::deleteRootZoneElement($id);
        Db::deleteTranslations($id, 'par_lang');

        $site->dispatchEvent('standard', 'languages', 'language_deleted', array('language_id'=>$id));    //deprecated
        $site->dispatchEvent('standard', 'languages', 'after_delte', array('language_id'=>$id));

    }


    function beforeUpdate($id) {
        global $site;

        $tmpLanguage = Db::getLanguageById($id);
        $this->urlBeforeUpdate = $tmpLanguage['url'];


        $site->dispatchEvent('standard', 'languages', 'before_update', array('language_id'=>$id));

    }


    function afterUpdate($id) {
        global $site;
        global $parametersMod;

        $tmpLanguage = Db::getLanguageById($id);
        if($tmpLanguage['url'] != $this->urlBeforeUpdate && $parametersMod->getValue('standard', 'languages', 'options', 'multilingual')) {
            $oldUrl = BASE_URL.$this->urlBeforeUpdate.'/';
            $newUrl = BASE_URL.$tmpLanguage['url'].'/';
            $site->dispatchEvent('administrator', 'system', 'url_change', array('old_url'=>$oldUrl, 'new_url'=>$newUrl));
        }

        $site->dispatchEvent('standard', 'languages', 'language_updated', array('language_id'=>$id));    //deprecated
        $site->dispatchEvent('standard', 'languages', 'after_update', array('language_id'=>$id));
    }

    function allowDelete($id) {
        global $parametersMod;
        require_once (MODULE_DIR."standard/menu_management/db.php");

        $dbMenuManagement = new \Modules\standard\menu_management\Db();

        $answer = true;


        $zones = Db::getZones();
        foreach($zones as $key => $zone) {
            $rootElement = $dbMenuManagement->rootContentElement($zone['id'], $id);
            $elements = $dbMenuManagement->pageChildren($rootElement);
            if(sizeof($elements) > 0) {
                $answer = false;
                $this->errors['delete'] = $parametersMod->getValue('standard', 'languages', 'admin_translations', 'cant_delete_not_empty_language');
            }
        }

        if(sizeof(Db::getLanguages()) ==1) {
            $answer = false;
            $this->errors['delete'] = $parametersMod->getValue('standard', 'languages', 'admin_translations', 'cant_delete_last_language');
        }


        return $answer;
    }

    function lastError($action) {
        if(isset($this->errors[$action]))
        return $this->errors[$action];
        else
        return '';
    }



}