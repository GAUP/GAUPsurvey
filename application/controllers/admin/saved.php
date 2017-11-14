<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
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
 * Saved controller
 *
 * @package LimeSurvey
 * @copyright 2011
  * @access public
 */
class saved extends Survey_Common_Action
{

    public function view($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $aViewUrls = array();

        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'responses', 'read'))
        {
            die();
        }

        $aThisSurvey = getSurveyInfo($iSurveyId);
        $aData['sSurveyName'] = $aThisSurvey['name'];
        $aData['iSurveyId'] = $iSurveyId;
        $aViewUrls[] = 'savedbar_view';
        $aViewUrls['savedlist_view'][] = $this->_showSavedList($iSurveyId);

        // saved.js bugs if table is empty
        if (count($aViewUrls['savedlist_view'][0]['aResults']))
        {
            App()->getClientScript()->registerPackage('jquery-tablesorter');
            $this->registerScriptFile( 'ADMIN_SCRIPT_PATH', 'saved.js');
        }


        $this->_renderWrappedTemplate('saved', $aViewUrls, $aData);
    }

    /**
     * Function responsible to delete saved responses.
     */
    public function delete($iSurveyId, $iSurveyResponseId, $iSavedControlId)
    {
        SavedControl::model()->deleteAllByAttributes(array('scid' => $iSavedControlId, 'sid' => $iSurveyId)) or die(gT("Couldn't delete"));
        Yii::app()->db->createCommand()->delete("{{survey_".intval($iSurveyId)."}}", 'id=:id', array('id' => $iSurveyResponseId)) or die(gT("Couldn't delete"));

        $this->getController()->redirect(array("admin/saved/sa/view/surveyid/{$iSurveyId}"));
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string[] $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'saved', $aViewUrls = array(), $aData = array())
    {
        $aData['display']['menu_bars']['browse'] = gT('Browse responses'); // browse is independent of the above
        $aData['surveyid'] = $iSurveyId = $aData['iSurveyId'];

        $surveyinfo = Survey::model()->findByPk($iSurveyId)->surveyinfo;
        $aData["surveyinfo"] = $surveyinfo;
        $aData['title_bar']['title'] = gT('Browse responses').': '.$surveyinfo['surveyls_title'];
        $aData['menu']['close'] =  true;
        $aData['menu']['edition'] = false;
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }

    /**
     * Load saved list.
     * @param mixed $iSurveyId Survey id
     */
    private function _showSavedList($iSurveyId)
    {
        $aResults = SavedControl::model()->findAll(array(
            'select' => array('scid', 'srid', 'identifier', 'ip', 'saved_date', 'email', 'access_code'),
            'condition' => 'sid=:sid',
            'order' => 'saved_date desc',
            'params' => array(':sid' => $iSurveyId),
        ));

        if (!empty($aResults))
        {
            return compact('aResults');
        }
        else
        {return array('aResults'=>array());}
    }

    public function geoshape_responses($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $aViewUrls = array();
        $questions = $this->get_geoshape_questions($iSurveyId);
        $responses = $oResponse = Response::model($iSurveyId)->findAll();
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'responses', 'read'))
        {
            die();
        }

        $aThisSurvey = getSurveyInfo($iSurveyId);
        $aData['sSurveyName'] = $aThisSurvey['name'];
        $aData['iSurveyId'] = $iSurveyId;
        $aData['QuestionsData'] = $questions;
        $aData['ResponsesData'] = $responses;
        $aViewUrls[] = 'savedbar_view';
        $aViewUrls[] = 'geoshape_responses_view';

        $this->_renderWrappedTemplate('saved', $aViewUrls, $aData);
    }

    public function get_geoshape_questions($iSurveyId) {
        $QuestionAttributeModel = QuestionAttribute::model();
        $Geoshape_question_type = Question::QUESTION_GEOSHAPE_TYPE;
        $condition              = "sid = '{$iSurveyId}' AND type = '{$Geoshape_question_type}' ";
        $geoshape_questions     = Question::model()->findAll($condition);

        foreach($geoshape_questions as $question) {
            $QuestionAttributeResult = $QuestionAttributeModel->findAll(
                "qid = :qid AND attribute IN ('location_defaultcoordinates', 'location_mapzoom', 'location_mapzoommax','location_mapservice') ",
                array(':qid' => $question->qid));
            $geoshape_questions_attr[$question->qid]['question'] = $question;

            if($QuestionAttributeResult) {
                foreach($QuestionAttributeResult as $question_attribute) {
                    $geoshape_questions_attr[$question->qid]['attributes'][$question_attribute->attribute] = $question_attribute->value;
                }
            }
        }

        return $geoshape_questions_attr;
    }

}
