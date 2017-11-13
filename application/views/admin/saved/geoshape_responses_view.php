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
    Yii::App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."geoshape_responses.js");
    Yii::app()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'geoshape_responses.css');
    Yii::App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts')."leaflet.ajax.min.js");
    
?>
<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <h3>
        <span style='font-weight:bold;'><?php eT('Saved GeoShape Responses:'); ?></span>
        <?php echo flattenText($sSurveyName) . ' ' . sprintf(gT('ID: %s'), $iSurveyId); ?>
    </h3>
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

        $gidquestion = $iSurveyId . $name;
        Yii::app()->getClientScript()->registerScript('sThisMapScriptVar' . $gidquestion,"LSmaps['{$gidquestion}']=".ls_json_encode($aThisMapScriptVar),CClientScript::POS_HEAD);
    ?>
    <div class="container">
    <div class="row">
    <div class="if-no-js">
        <!-- No javascript need a way to answer -->

        <input
            type="hidden"
            name="<?php echo $name; ?>"
            id="answer<?php echo $name; ?>"
            value="<?php echo $value; ?>"
            >

            <input
            type="hidden"
            class="location"
            name="<?php echo $iSurveyId . $name; ?>"
            id="answer<?php echo $name; ?>"
            value="<?php echo $location_value; ?>"
            />

            <!-- <ul class="list-unstyled coordinates-list col-xs-12"> -->
                <!-- <li class="coordinate-item"> -->
                    <!-- <?php eT("Latitude:"); ?> -->
                    <input
                    class="coords text"
                    type="text"
                    name="<?php echo $name; ?>_c1"
                    id="answer_lat<?php echo $name; ?>_c"
                    value="<?php echo $currentLat; ?>"
                    />
                <!-- </li> -->

                <!-- <li class="coordinate-item"> -->
                    <!-- <?php eT("Longitude:"); ?> -->
                    <input
                    class="coords text"
                    type="text"
                    name="<?php echo $name; ?>_c2"
                    id="answer_lng<?php echo $name; ?>_c"
                    value="<?php echo $currentLong; ?>"
                    />
                <!-- </li> -->
            <!-- </ul> -->

            <input
            type="hidden"
            name="boycott_<?php echo $name; ?>"
            id="boycott_<?php echo $name; ?>"
            value = "<?php echo $strBuild; ?>"
            />

            <input
            type="hidden"   
            name="mapservice<?php echo $iSurveyId . $name?>"
            id="mapservice<?php echo $iSurveyId . $name?>"
            class="mapservice"
            value="<?php echo $location_mapservice; ?>"
            />
        </div>

        <!-- Map -->
            <div id="map_<?php echo $iSurveyId . $name ?>" class="col-xs-12 map_responses">

            </div>
        </div>
        </div>
    <?php }?>
</div>
