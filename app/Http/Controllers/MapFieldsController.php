<?php


namespace App\Http\Controllers;
use Storage;
use DB;


class MapFieldsController extends Controller{

    public function ShopifyMapFields($data, $mappingFields)
    {
        $enterLoop = 0 ;
        $PostData = [];

        foreach ($mappingFields as $vboutFields => $shopifyFields) {
            try{
                $shopifyFieldsLayers = explode('|', $shopifyFields);
                if(count($shopifyFieldsLayers) == 4) {
                    if (!isset($data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]][$shopifyFieldsLayers[2]][$shopifyFieldsLayers[3]]) || $data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]][$shopifyFieldsLayers[3]] == null)
                    {
                        if (isset($data[$shopifyFieldsLayers[0]]) && isset($data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]]) && count($data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]][$shopifyFieldsLayers[2]]) > 1){
                            $enterLoop = 0;
                        }
                        else {
                            $enterLoop = 1;
                        }

                        if ($enterLoop == 1) {
                            $shopifyFieldValueRecord = $data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]][$shopifyFieldsLayers[2]];
                            foreach ($shopifyFieldValueRecord as $itemkey => $shopifyFieldRecordValue)
                            {
                                $PostData[$shopifyFieldsLayers[1]][$itemkey][$vboutFields] = $shopifyFieldValueRecord[$itemkey][$shopifyFieldsLayers[2]][$shopifyFieldsLayers[2]];
                            }
                        }
                        else $PostData[$vboutFields] = $data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]][$shopifyFieldsLayers[2]][$shopifyFieldsLayers[3]];
                    }
                    else $PostData[$vboutFields] = $data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]][$shopifyFieldsLayers[2]][$shopifyFieldsLayers[3]];
                }
                else if(count($shopifyFieldsLayers) == 3) {
                    if (!isset($data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]][$shopifyFieldsLayers[2]]) || $data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]] == null)
                    {
                        if (isset($data[$shopifyFieldsLayers[0]]) && isset($data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]]) && count($data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]]) > 1){
                            $enterLoop = 0;
                        }
                        else {
                            $enterLoop = 1;
                        }

                        if ($enterLoop == 1) {
                            $shopifyFieldValueRecord = isset($data[$shopifyFieldsLayers[0]]) && isset($data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]]) ? $data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]] : array();
                            foreach ($shopifyFieldValueRecord as $itemkey => $shopifyFieldRecordValue)
                            {
                                $PostData[$shopifyFieldsLayers[1]][$itemkey][$vboutFields] = $shopifyFieldValueRecord[$itemkey][$shopifyFieldsLayers[2]];
                            }
                        }
                        else $PostData[$vboutFields] = $data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]][$shopifyFieldsLayers[2]];
                    }
                    else {
                        $PostData[$vboutFields] = $data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]][$shopifyFieldsLayers[2]];
                    }
                }
                else if(count($shopifyFieldsLayers) == 2) {
                    if(!isset($data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]]) || $data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]] == null) {
                        if (isset($data[$shopifyFieldsLayers[0]]) && count($data[$shopifyFieldsLayers[0]]) > 1){
                            $enterLoop = 0;
                        }
                        else {
                            $enterLoop = 1;
                        }

                        if ($enterLoop == 1) {
                            $shopifyFieldValueRecord = isset($data[$shopifyFieldsLayers[0]]) ? $data[$shopifyFieldsLayers[0]] : array();
                            foreach($shopifyFieldValueRecord as $itemkey => $shopifyFieldRecordValue)
                            {
                                $PostData[$vboutFields] =$shopifyFieldRecordValue[$shopifyFieldsLayers[1]];
                            }
                        }
                        else $PostData[$vboutFields] = $data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]];
                    }
                    else $PostData[$vboutFields] = $data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]];
                }
                else if(count($shopifyFieldsLayers) == 1) {
                    $PostData[$vboutFields] = $data[$shopifyFieldsLayers[0]];
                }
            }
             catch (\Exception $ex) {
                DB::table('logging')->insert(
                    [
                        'data' => $ex->getMessage() . ' file: ' . $ex->getFile() . ' line: ' . $ex->getLine(),
                        'step' => 2,
                        'comment' => 'Shopify Mapping Fields Error'
                    ]
                );
            }
        }

        DB::table('logging')->insert(
            [
                'data' => (serialize($PostData)),
                'step' => 2,
                'comment' => 'Inserting post data in Shopify Mapping fields'
            ]
        );

        return $PostData;
    }

}