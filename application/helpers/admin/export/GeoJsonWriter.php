<?php
class GeoJsonWriter extends Writer
{
    private $responses_geojson;

    private $survey_id;

    private $zip_geojson_file;

    function __construct()
    {
        $this->output = '';
    }

    public function init(SurveyObj $survey, $sLanguageCode, FormattingOptions $oOptions) {
        parent::init($survey, $sLanguageCode, $oOptions);
        $this->survey_id = $survey->id;
    }

    protected function outputRecord($headers, $values, FormattingOptions $oOptions) {
        $values_geojson = array();

        foreach($values as $key => $value_column) {
            if(is_object(json_decode($value_column))) {
                $values_geojson = json_decode($value_column);
                $question_id = $values_geojson->features[0]->properties->question_code;
                if(!isset($this->responses_geojson[$question_id])) {
                    $this->responses_geojson["responses"][$question_id]["type"] = "FeatureCollection";
                }
                $this->responses_geojson["responses"][$question_id]["features"][] = $values_geojson->features[0];
            };
        }
    }

    public function close() {
        Yii::app()->loadLibrary("admin/pclzip");
        $this->zip_geojson_file = $this->survey_id . "_" . date('Ymdhi') . ".zip";
        $zip_folder = Yii::app()->getConfig("tempdir") . DIRECTORY_SEPARATOR;

        $zip = new PclZip($this->zip_geojson_file);

        header("Content-Type: application/x-zip");
        header("Content-Disposition: attachment; filename=" . $this->zip_geojson_file);
        
        foreach($this->responses_geojson["responses"] as $question_id => $responses) {
            $this->filename = Yii::app()->getConfig("tempdir") . DIRECTORY_SEPARATOR . $this->survey_id . "_" . $question_id . ".geojson";
            $this->file = "";
            $this->output = json_encode($responses);
                $this->file = fopen($this->filename, 'w');
                fwrite($this->file, $this->output);
                fclose($this->file);

                 $zip->add(
                    array(
                        array(
                            PCLZIP_ATT_FILE_NAME => $this->survey_id . "_" . $question_id . ".geojson",
                            PCLZIP_ATT_FILE_NEW_FULL_NAME => $this->survey_id . "_" . $question_id . ".geojson"
                        )
                    )
                );
                unlink($this->filename);
        }
        header("Content-Length: " . filesize($this->zip_geojson_file));
        readfile($this->zip_geojson_file);
        unlink($this->zip_geojson_file);
    }
}
