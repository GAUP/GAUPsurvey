<?php
    $aGlobalMapScriptVar = array(
        'geonameUser' => getGlobalSetting('GeoNamesUsername'),// Did we need to urlencode ?
        'geonameLang' => Yii::app()->language,
    );

    Yii::App()->getClientScript()->registerPackage('leaflet');
    Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."leaflet.toolbar-src.js");
    Yii::app()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'leaflet.toolbar.css');
    Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."leaflet.draw-src.js");
    Yii::app()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'leaflet.draw.css');
    Yii::app()->getClientScript()->registerScript('sGlobalMapScriptVar',"LSmap=".ls_json_encode($aGlobalMapScriptVar).";\nLSmaps= new Array();",CClientScript::POS_HEAD);
    Yii::app()->getClientScript()->registerScript('sGlobalMapJSONResponses',"\nMapJSONResponses = new Array();",CClientScript::POS_HEAD);
    Yii::App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."geoshape_responses.js");
    Yii::app()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'geoshape_responses.css');
    Yii::App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."leaflet.ajax.min.js");
    
?>
<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <h3>
        <span style='font-weight:bold;'><?php eT('Saved GeoShape Responses:'); ?></span>
        <?php echo flattenText($sSurveyName) . ' ' . sprintf(gT('ID: %s'), $iSurveyId); ?>
    </h3>
    <div class="container">
        <div class="row">
            <div class="if-no-js">
                <input
                    type="hidden"
                    class="location"
                    name="<?php echo $iSurveyId; ?>"
                    id="location_<?php echo $iSurveyId; ?>"
                    value="<?php echo $location_value; ?>"
                    />
                
                <input
                    type="hidden"   
                    name="mapservice<?php echo $iSurveyId; ?>"
                    id="mapservice<?php echo $iSurveyId; ?>"
                    class="mapservice"
                    value="100"
                    />
            </div>

            <!-- Map -->
            <div id="map_<?php echo $iSurveyId; ?>" class="col-xs-12 map_responses">

            </div>
        </div>
    </div>

    <?php
    foreach($QuestionsData as $question) {
        $name               = $question['question']->title;
        $location_value     = $question['attributes']['location_defaultcoordinates'];
        $location           = explode(" ",$location_value);
        $currentLat         = $location[0];
        $currentLong        = $location[1];
        $location_mapservice = $question['attributes']['location_mapservice'];

        $aThisMapScriptVar = array(
            'zoomLevel' => $question['attributes']['location_mapzoom'],
            'latitude'  => $currentLat,
            'longitude' => $currentLong
        );

        Yii::app()->getClientScript()->registerScript('sLocationMapScriptVar' . $iSurveyId,"document.getElementById('location_{$iSurveyId}').value = '{$question['attributes']['location_defaultcoordinates']}';",CClientScript::POS_END);
        Yii::app()->getClientScript()->registerScript('sThisMapScriptVar' . $iSurveyId,"LSmaps['{$iSurveyId}']=".ls_json_encode($aThisMapScriptVar) . ";",CClientScript::POS_HEAD);

        $gidquestion = $iSurveyId . $name;
                
        $responseColumn = $iSurveyId . 'X' . $question['question']->gid . 'X' . $question['question']->qid;
        foreach($ResponsesData as $oResponse) {

            $json_response = json_decode($oResponse->{$responseColumn});
            if(count($json_response->features) > 0) {
                $response_features[] = $json_response->features[0];
            }
        }
        $MapJSONResponses[$question['question']->title]["type"] = "FeatureCollection";
        $MapJSONResponses[$question['question']->title]["features"] = $response_features;
    }
    Yii::app()->getClientScript()->registerScript('sMapJSONResponses' . $iSurveyId,"\nMapJSONResponses = " . json_encode($MapJSONResponses) . ";",CClientScript::POS_HEAD);
    ?>
</div>
