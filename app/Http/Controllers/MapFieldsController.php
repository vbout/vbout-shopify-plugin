<?php


namespace App\Http\Controllers;
use Storage;
use DB;


class MapFieldsController extends Controller
{

    public function ShopifyMapFields($data, $mappingFields)
    {

        $enterLoop = 0 ;
        $PostData = [];
        foreach ($mappingFields as $vboutFields => $shopifyFields)
        {

            $shopifyFieldsLayers = explode('|', $shopifyFields);
            if(count($shopifyFieldsLayers) == 4) {
                if (!isset($data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]][$shopifyFieldsLayers[2]][$shopifyFieldsLayers[3]]) || $data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]][$shopifyFieldsLayers[3]] == null)
                {
                    if (count($data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]][$shopifyFieldsLayers[2]]) > 1)
                        $enterLoop = 0;
                    else $enterLoop = 1;
                    if ($enterLoop == 1)
                    {
                        $shopifyFieldValueRecord = $data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]][$shopifyFieldsLayers[2]];
                        foreach ($shopifyFieldValueRecord as $itemkey => $shopifyFieldRecordValue)
                        {
                            $PostData[$shopifyFieldsLayers[1]][$itemkey][$vboutFields] = $shopifyFieldValueRecord[$itemkey][$shopifyFieldsLayers[2]][$shopifyFieldsLayers[2]];
                        }
                    } else $PostData[$vboutFields] = $data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]][$shopifyFieldsLayers[2]][$shopifyFieldsLayers[3]];
                }
                else $PostData[$vboutFields] = $data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]][$shopifyFieldsLayers[2]][$shopifyFieldsLayers[3]];
            }
            if(count($shopifyFieldsLayers) == 3) {

                if (!isset($data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]][$shopifyFieldsLayers[2]]) || $data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]] == null)
                {
                    if (count($data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]]) > 1)
                        $enterLoop = 0;
                    else $enterLoop = 1;
                    if ($enterLoop == 1)
                    {
                        $shopifyFieldValueRecord = $data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]];
                        foreach ($shopifyFieldValueRecord as $itemkey => $shopifyFieldRecordValue)
                        {
                            $PostData[$shopifyFieldsLayers[1]][$itemkey][$vboutFields] = $shopifyFieldValueRecord[$itemkey][$shopifyFieldsLayers[2]];
                        }
                    } else $PostData[$vboutFields] = $data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]][$shopifyFieldsLayers[2]];
                }
                else {
                     $PostData[$vboutFields] = $data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]][$shopifyFieldsLayers[2]];
                }
            }
            else if(count($shopifyFieldsLayers) == 2)
            {
                if(!isset($data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]]) || $data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]] == null)
                {
                     if (count($data[$shopifyFieldsLayers[0]]) > 1)
                        $enterLoop = 0;
                    else $enterLoop = 1;
                    if ($enterLoop == 1)
                    {
                        $shopifyFieldValueRecord = $data[$shopifyFieldsLayers[0]];
                        foreach($shopifyFieldValueRecord as $itemkey => $shopifyFieldRecordValue)
                        {
                            $PostData[$vboutFields] =$shopifyFieldRecordValue[$shopifyFieldsLayers[1]];
                        }
                    }
                    else $PostData[$vboutFields] = $data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]];
                }
                else $PostData[$vboutFields] = $data[$shopifyFieldsLayers[0]][$shopifyFieldsLayers[1]];
            }
            else if(count($shopifyFieldsLayers) == 1)
            {
                $PostData[$vboutFields] = $data[$shopifyFieldsLayers[0]];
            }

        }

        return $PostData;
    }


}