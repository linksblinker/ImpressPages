<?php
/**
 * @package ImpressPages

 *
 */
namespace Ip\Internal\Content\Widget\Image;




class Controller extends \Ip\WidgetController{


    public function getTitle() {
        return __('Image', 'ipAdmin', false);
    }


    public function update($widgetId, $postData, $currentData) {

        if (isset($postData['method'])) {
            switch($postData['method']) {
                case 'newImage':
                    $newData = $currentData;

                    if (isset($postData['newImage']) && is_file(ipFile('file/repository/' . $postData['newImage']))) {
                        //unbind old image
                        if (isset($currentData['imageOriginal']) && $currentData['imageOriginal']) {
                            \Ip\Internal\Repository\Model::unbindFile($currentData['imageOriginal'], 'Content', $widgetId);
                        }

                        //bind new image
                        \Ip\Internal\Repository\Model::bindFile($postData['newImage'], 'Content', $widgetId);

                        $newData['imageOriginal'] = $postData['newImage'];
                    }

                    return $newData;
                    break;
                case 'resize':
                    $newData = $currentData;
                    if (!isset($postData['width']) || !$postData['height']) {
                        ipLog()->error("Image widget resize missing required parameter", $postData);
                        throw new \Ip\Exception("Missing required data");
                    }
                    $newData['width'] = $postData['width'];
                    $newData['height'] = $postData['height'];
                    return $newData;
                    break;
                case 'autosize':
                    unset($currentData['width']);
                    unset($currentData['height']);
                    return $currentData;
                    break;
                case 'update':
                    $newData = $currentData;
                    if (isset($postData['cropX1']) && isset($postData['cropY1']) && isset($postData['cropX2']) && isset($postData['cropY2'])) {
                        $curWidth = $postData['cropX2'] - $postData['cropX1'];
                        $curHeight = $postData['cropY2'] - $postData['cropY1'];
                        //new small image
                        $newData['cropX1'] = $postData['cropX1'];
                        $newData['cropY1'] = $postData['cropY1'];
                        $newData['cropX2'] = $postData['cropX2'];
                        $newData['cropY2'] = $postData['cropY2'];
                        if (!isset($newData['width'])) {
                            $newData['width'] = $curWidth;
                            $newData['height'] = $curHeight;
                        } else {
                            if ($curWidth != 0) {
                                $newData['height'] = $curHeight / $curWidth * $currentData['width'];
                            }

                        }
                    }
                    return $newData;

                    break;
            }
        }
    }


    public function delete($widgetId, $data) {
        self::_deleteImage($data, $widgetId);
    }

    private function _deleteImage($data, $widgetId) {
        if (!is_array($data)) {
            return;
        }
        if (isset($data['imageOriginal']) && $data['imageOriginal']) {
            \Ip\Internal\Repository\Model::unbindFile($data['imageOriginal'], 'Content', $widgetId);
        }
    }


    /**
    *
    * Duplicate widget action. This function is executed after the widget is being duplicated.
    * All widget data is duplicated automatically. This method is used only in case a widget
    * needs to do some maintenance tasks on duplication.
    * @param int $oldId old widget id
    * @param int $newId duplicated widget id
    * @param array $data data that has been duplicated from old widget to the new one
    */
    public function duplicate($oldId, $newId, $data) {
        if (!is_array($data)) {
            return;
        }
        if (isset($data['imageOriginal']) && $data['imageOriginal']) {
            \Ip\Internal\Repository\Model::bindFile($data['imageOriginal'], 'Content', $newId);
        }
    }



    public function generateHtml($revisionId, $widgetId, $instanceId, $data, $skin) {
        if (isset($data['imageOriginal'])) {
            $desiredName = isset($data['title']) ? $data['title'] : 'image';

            $transformBig = new \Ip\Transform\None();
            $data['imageBig'] = ipReflection($data['imageOriginal'], $desiredName, $transformBig);




            if (
                isset($data['cropX1']) && isset($data['cropY1']) && isset($data['cropX2']) && isset($data['cropY2'])
                && $data['cropY2'] - $data['cropY1'] > 0
            ) {
                if (!empty($data['width'])) {
                    $width = $data['width'];
                } else {
                    $width = ipGetOption('Content.widgetImageWidth', 1200);
                }
                if ($width <= 0) {
                    $width = 1200;
                }


                if (!empty($data['height'])) {
                    $height = $data['height'];
                } else {
                    $ratio = ($data['cropX2'] - $data['cropX1']) / ($data['cropY2'] - $data['cropY1']);
                    if ($ratio == 0) {
                        $ratio = 1;
                    }
                    $height = round($width / $ratio);
                }
                $transform = new \Ip\Transform\ImageCrop(
                    $data['cropX1'],
                    $data['cropY1'],
                    $data['cropX2'],
                    $data['cropY2'],
                    $width,
                    $height
                );
                $data['imageSmall'] = ipFileUrl(ipReflection($data['imageOriginal'], $desiredName, $transform));
            } else {
                if (!empty($data['width'])) {
                    $width = $data['width'];
                } else {
                    $width = ipGetOption('Content.widgetImageWidth', 1200);
                }

                if (!empty($data['height'])) {
                    $height = $data['height'];
                } else {
                    $height = ipGetOption('Content.widgetImageHeight', 900);
                }
                $transform = new \Ip\Transform\ImageFit(
                    $width,
                    $height
                );
            }
            try {
                $data['imageSmall'] = ipFileUrl(ipReflection($data['imageOriginal'], $desiredName, $transform));
            } catch (\Ip\Exception\TransformException $e) {
                ipLog()->error($e->getMessage(), array('errorTrace' => $e->getTraceAsString()));
            }
        }
        return parent::generateHtml($revisionId, $widgetId, $instanceId, $data, $skin);
    }



}
