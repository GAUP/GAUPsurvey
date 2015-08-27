<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

/**
 * Class SurveyLanguageSetting
 * @property string $surveyls_title
 * @property string $surveyls_description
 * @property string $surveyls_welcometext
 * @property string $surveyls_endtext
 * @property string $surveyls_url
 * @property string $surveyls_urldescription
 * @property string $surveyls_language
 * @property int $surveyls_dateformat
 * @property int $surveyls_numberformat
 */
class SurveyLanguageSetting extends ActiveRecord
{
    public function attributeLabels()
    {
        return [
            'surveyls_title' => gT("Survey title"),
            'surveyls_endtext' => gT("End message"),
            'surveyls_welcometext' => gT('Welcome message'),
            'surveyls_description' => gT('Description'),
            'surveyls_url' => gT("End URL"),
            'surveyls_urldescription' => gT('URL description'),
            'surveyls_dateformat' => gT('Date format'),
            'surveyls_numberformat' => gT('Decimal mark'),

        ];
    }

    /**
     * Returns the table's name
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{surveys_languagesettings}}';
    }

    /**
     * Returns the relations of this model
     *
     * @access public
     * @return array
     */
    public function relations()
    {
        $alias = $this->getTableAlias();
        return array(
            'survey' => array(self::BELONGS_TO, Survey::class, 'surveyls_survey_id'),
            'owner' => array(self::BELONGS_TO, 'User', '', 'on' => 'survey.owner_id = owner.uid'),
        );
    }


    /**
    * Returns this model's validation rules
    *
    */
    public function rules()
    {
        return array(
            array('surveyls_email_invite_subj','lsdefault'),
            array('surveyls_email_invite','lsdefault'),
            array('surveyls_email_remind_subj','lsdefault'),
            array('surveyls_email_remind','lsdefault'),
            array('surveyls_email_confirm_subj','lsdefault'),
            array('surveyls_email_confirm','lsdefault'),
            array('surveyls_email_register_subj','lsdefault'),
            array('surveyls_email_register','lsdefault'),
            array('email_admin_notification_subj','lsdefault'),
            array('email_admin_notification','lsdefault'),
            array('email_admin_responses_subj','lsdefault'),
            array('email_admin_responses','lsdefault'),

            array('surveyls_email_invite_subj','LSYii_Validators'),
            array('surveyls_email_invite','LSYii_Validators'),
            array('surveyls_email_remind_subj','LSYii_Validators'),
            array('surveyls_email_remind','LSYii_Validators'),
            array('surveyls_email_confirm_subj','LSYii_Validators'),
            array('surveyls_email_confirm','LSYii_Validators'),
            array('surveyls_email_register_subj','LSYii_Validators'),
            array('surveyls_email_register','LSYii_Validators'),
            array('email_admin_notification_subj','LSYii_Validators'),
            array('email_admin_notification','LSYii_Validators'),
            array('email_admin_responses_subj','LSYii_Validators'),
            array('email_admin_responses','LSYii_Validators'),

            array('surveyls_title','LSYii_Validators'),
            array('surveyls_description','LSYii_Validators'),
            array('surveyls_welcometext','LSYii_Validators'),
            array('surveyls_endtext','LSYii_Validators'),
            array('surveyls_url','LSYii_Validators','isUrl'=>true),
            array('surveyls_urldescription','LSYii_Validators'),

            array('surveyls_dateformat', 'numerical', 'integerOnly'=>true, 'min'=>'1', 'max'=>'12', 'allowEmpty'=>true),
            array('surveyls_numberformat', 'numerical', 'integerOnly'=>true, 'min'=>'0', 'max'=>'1', 'allowEmpty'=>true),
        );
    }


    /**
    * Defines the customs validation rule lsdefault
    *
    * @param mixed $attribute
    * @param mixed $params
    */
    public function lsdefault($attribute,$params)
    {
        if (isset($this->surveyls_survey_id)) {
            $oSurvey = $this->survey;
            $sEmailFormat = $oSurvey->htmlemail == 'Y' ? 'html' : '';
            $aDefaultTexts = templateDefaultTexts($this->surveyls_language, 'unescaped', $sEmailFormat);

            $aDefaultTextData = array(
                'surveyls_email_invite_subj' => $aDefaultTexts['invitation_subject'],
                'surveyls_email_invite' => $aDefaultTexts['invitation'],
                'surveyls_email_remind_subj' => $aDefaultTexts['reminder_subject'],
                'surveyls_email_remind' => $aDefaultTexts['reminder'],
                'surveyls_email_confirm_subj' => $aDefaultTexts['confirmation_subject'],
                'surveyls_email_confirm' => $aDefaultTexts['confirmation'],
                'surveyls_email_register_subj' => $aDefaultTexts['registration_subject'],
                'surveyls_email_register' => $aDefaultTexts['registration'],
                'email_admin_notification_subj' => $aDefaultTexts['admin_notification_subject'],
                'email_admin_notification' => $aDefaultTexts['admin_notification'],
                'email_admin_responses_subj' => $aDefaultTexts['admin_detailed_notification_subject'],
                'email_admin_responses' => $aDefaultTexts['admin_detailed_notification']
            );
            if ($sEmailFormat == "html") {
                $aDefaultTextData['admin_detailed_notification'] = $aDefaultTexts['admin_detailed_notification_css'] . $aDefaultTexts['admin_detailed_notification'];
            }

            if (empty($this->$attribute)) {
                $this->$attribute = $aDefaultTextData[$attribute];
            }
        }
    }


    function getDateFormat($surveyid,$languagecode)
    {
        return Yii::app()->db->createCommand()->select('surveyls_dateformat')
            ->from('{{surveys_languagesettings}}')
            ->join('{{surveys}}','{{surveys}}.sid = {{surveys_languagesettings}}.surveyls_survey_id AND surveyls_survey_id = :surveyid')
            ->where('surveyls_language = :langcode')
            ->bindParam(":langcode", $languagecode, PDO::PARAM_STR)
            ->bindParam(":surveyid", $surveyid, PDO::PARAM_INT)
            ->queryScalar();
    }


    function insertNewSurvey($data)
    {
        return $this->insertSomeRecords($data);
    }

    function insertSomeRecords($data)
    {
        $lang = new self;
        foreach ($data as $k => $v)
            $lang->$k = $v;
        return $lang->save();
    }

    public function getDateFormatOptions() {
        return array_map(function($e) { return $e['dateformat']; }, getDateFormatData());
    }
    public function getNumberFormatOptions() {
        return array_map(function($e) { return $e['desc']; }, getRadixPointData());
    }
}
