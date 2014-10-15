<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 10/11/14
 * Time: 12:00 PM
 */

namespace Main\CTL;
use Main\Exception\Service\ServiceException;
use Main\Helper\ArrayHelper;
use Main\Helper\MongoHelper;
use Main\Helper\NodeHelper;
use Main\Service\FeedService;
use Main\Service\OverviewPromotionService;

/**
 * @Restful
 * @uri /feed
 */
class FeedCTL extends BaseCTL {
    /**
     * @GET
     */
    public function gets(){
        try {
            $items = FeedService::getInstance()->gets($this->reqInfo->params(), $this->getCtx());
            foreach($items['data'] as $key=> $item){
                if($item['type']=='news'){
                    $item = OverviewPromotionService::getInstance()->get($item['_id'], $this->getCtx());
                    MongoHelper::standardIdEntity($item);
                    $item['created_at'] = MongoHelper::timeToInt($item['created_at']);
                    $item['updated_at'] = MongoHelper::timeToInt($item['updated_at']);
                    ArrayHelper::pictureToThumb($item);

                    // translate
                    if($this->getCtx()->getTranslate()){
                        ArrayHelper::translateEntity($item, $this->getCtx()->getLang());
                    }

                    // make node
                    $item['node'] = NodeHelper::overviewPromotion($item['id']);
                }

                $items['data'][$key] = $item;
            }
            return $items;
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }
}