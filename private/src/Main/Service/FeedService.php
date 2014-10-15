<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 10/11/14
 * Time: 4:47 PM
 */

namespace Main\Service;


use Main\Context\Context;
use Main\DB;
use Main\Helper\URL;

class FeedService extends BaseService {
    public function getCollection(){
        return DB::getDB()->feed;
    }

    public function getOverviewPromotionCollection(){
        return DB::getDB()->overview_promotion;
    }

    public function gets($params, Context $ctx){
        $default = array(
            "page"=> 1,
            "limit"=> 15,
        );
        $options = array_merge($default, $params);

        $skip = ($options['page']-1)*$options['limit'];
        $condition = [];

        $cursor = $this->getCollection()
            ->find($condition)
            ->limit((int)$options['limit'])
            ->skip((int)$skip)
            ->sort(['created_at'=> -1]);

        $data = [];

        foreach($cursor as $item){
            $data[] = $item;
        }

        $total = $this->getCollection()->count($condition);
        $length = $cursor->count(true);

        $res = [
            'length'=> $length,
            'total'=> $total,
            'data'=> $data,
            'paging'=> [
                'page'=> (int)$options['page'],
                'limit'=> (int)$options['limit']
            ]
        ];

        $pagingLength = $total/(int)$options['limit'];
        $pagingLength = floor($pagingLength)==$pagingLength? floor($pagingLength): floor($pagingLength) + 1;
        $res['paging']['length'] = $pagingLength;
        $res['paging']['current'] = (int)$options['page'];
        if(((int)$options['page'] * (int)$options['limit']) < $total){
            $nextQueryString = http_build_query(['page'=> (int)$options['page']+1, 'limit'=> (int)$options['limit']]);
            $res['paging']['next'] = URL::absolute('/overview/promotion'.'?'.$nextQueryString);
        }

        return $res;
    }
}