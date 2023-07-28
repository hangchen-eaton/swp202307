<?php

namespace app\controller\promotion;

use app\admin\model\Message;
use app\controller\businessopportunity\Businessopportunity;
use app\controller\businessopportunity\User;
use app\model\businessopportunity\PermModel;
use app\model\businessopportunity\ShopBaseModel;
use app\model\promotion\CombinedModel;
use app\model\order\ShopModel;
use app\model\message\MessageModel;
use app\model\promotion\PromotionModel;
use app\model\promotion\RulesModel;
use app\model\promotion\TaskModel;
use Exception;
use think\facade\Db;


class Activity extends User
{
    #region添加指定任务促销
    public function addAssignTask(PromotionModel $promotionModel, TaskModel $taskModel, RulesModel $rulesModel)
    {
        $post = input('post.');
        $date = date('Y-m-d H:i:s');
        $pro_arr = [
            'combination_name' => $post['combination_name'],
            'create_user_id' => $this->user_id,
            'update_user_id' => $this->user_id,
            'activity_type' => 2,
            'effective_date' => $post['effective_date'],
            'end_date' => date('Y-m-d', strtotime($post['end_date'])+ (60*60*24)),
            'create_time' => $date,
            'update_time' => $date,
            'shop_id' => 2,
            'approval_status' => 1,
            'release_status' => 1,
        ];
        try {
            $promotionModel->startTrans();

            if (empty($post['promotion_id'])) {
                $count = $promotionModel->getCount(['combination_name' => $post['combination_name'], 'is_delete' => 1]);
                if ($count > 0) {
                    return json(msg(400, '', '组合名称已存再，请勿重复添加'));
                }
                $promotion_id = $promotionModel->getAddDataId($pro_arr);

                if (!$promotion_id) {
                    throw  new \think\Exception('促销活动添加失败');
                }
            } else {
                $find = $promotionModel->getFind(['combination_name' => $post['combination_name'], 'is_delete' => 1]);

                if (!empty($find) && $find['id'] != $post['promotion_id']) {
                    return json(msg(400, '', '组合名称已存再，请勿重复添加'));
                }
                unset($pro_arr['create_time']);
                unset($pro_arr['create_user_id']);

                $promotionModel->updates(['id' => $post['promotion_id']], $pro_arr);
                $taskModel->delData(['promotion_id' => $post['promotion_id']]);
                $rulesModel->delData(['promotion_id' => $post['promotion_id']]);
                $promotion_id = $post['promotion_id'];
            }
//        $post['combined'] = [
//            ['product_id'=>1,'product_series_id'=>1,'product_line_id'=>1],
//            ['product_id'=>2,'product_series_id'=>2,'product_line_id'=>2],
//            ['product_id'=>3,'product_series_id'=>3,'product_line_id'=>3],
//        ];

            if (!is_array($post['combined'])) {
                throw new \think\Exception('商品格式错误');
            }
            foreach ($post['combined'] as $k => $v) {
                if (empty($v['product_id'])) {
                    throw new \think\Exception('缺少商品参数产品编号');
                }
                if (empty($v['product_series_id'])) {
                    throw new \think\Exception('缺少商品参数产品系列编号');
                }
                if (empty($v['product_line_id'])) {
                    throw new \think\Exception('缺少商品参数产品PL8编号');
                }
                $task_arr[] = [
                    'promotion_id' => $promotion_id,
                    'product_id' => $v['product_id'],
                    'product_series_id' => $v['product_series_id'],
                    'product_line_id' => $v['product_line_id'],
                    'common_price' => $v['common_price'] ?? '',
                ];
            }

            $add = $taskModel->addAll($task_arr);
            if (!$add) {
                throw new \think\Exception('商品添加失败');
            }

            if (!is_array($post['rules'])) {
                throw new \think\Exception('任务促销规则格式错误');
            }
            if (!empty($post['rules'])) {
                $participantsIds = [];
                $username_arr = [];
                foreach ($post['rules'] as $k => $v) {
                    $tmp = [
                        'promotion_id' => $promotion_id,
                        'participants_id' => $v['participants_id'] ?? '',
                        'mission_objectives' => $v['mission_objectives'] ?? '',
                        'overall_proportion' => $v['overall_proportion'] ?? 0,
                        'individual_proportion' => $v['individual_proportion'] ?? 0,
                        'rebate_ratio' => $v['rebate_ratio'] ?? 0,
                        'integral_proportion' => $v['integral_proportion'] ?? 0,
                        'participants_name' => $v['participants_name'] ?? ''
                    ];

                    // TODO 根据participants_id 重置 participants_name
                    if($v['participants_id']){
                        $participantsIds[] = $v['participants_id'];
                        $shopNames = ShopModel::whereRaw('shop_id in ('.$v['participants_id'].')')->field('shop_name')->select()->toArray();
                        $tmp['participants_name'] =implode(',',array_column($shopNames,'shop_name'));
                    }

                    $rule[] = $tmp;
                }
                $addRules = $rulesModel->addAll($rule);
                if (!$addRules) {
                    throw new \think\Exception('指定任务促销规则添加失败');
                }

                $rights_group = Db::table('yf_admin_rights_group')->where('rights_group_name', '产品总监')->find();
                $user_list = Db::table('lncrm_auth_group_access')->where('group_id', $rights_group['rights_group_id'])->column('uid');
                if($user_list){
                    $message = new \app\model\message\MessageModel();
                    foreach ($user_list as $k => $v){
                        if($v != $this->user_id)
                        $message->message_send('新营销组合待审批', '商品', 1,$v , [$post['combination_name']], ['yf_sales_promotion', $post['combination_name']]);
                    }
                }

                $promotionModel->updates(['id' => $promotion_id], ['participants_id'=>implode(',',$participantsIds)]);//写入participants_id
            }
            $promotionModel->commit();
            return json(msg(200, '', '成功'));
        } catch (\Exception $e) {
            $promotionModel->rollback();
            return json(msg(400, '', $e->getMessage()));
        }
    }
    #endregion

    #region添加组合促销
    public function addCombined(PromotionModel $promotionModel, CombinedModel $combinedModel)
    {
        $post = input('post.');
        $date = date('Y-m-d H:i:s');
        $pro_arr = [
            'combination_name' => $post['combination_name'],
            'create_user_id' => $this->user_id,
            'update_user_id' => $this->user_id,
            'activity_type' => 1,
            'effective_date' => $post['effective_date'],
            'end_date' => date('Y-m-d', strtotime($post['end_date'])+ (60*60*24)),
            'participants_id' => $post['participants_id'],
            'create_time' => $date,
            'update_time' => $date,
            'participants_name' => $post['participants_name'] ?? '',
            'shop_id' => 2,
            'approval_status' => 1,
            'release_status' => 1,
        ];
        // TODO 根据participants_id 重置 participants_name

        // TODO 根据participants_id 重置 participants_name
        if($post['participants_id']){
            $shopNames = ShopModel::whereRaw('shop_id in ('.$post['participants_id'].')')->field('shop_name')->select()->toArray();
            $pro_arr['participants_name'] =implode(',',array_column($shopNames,'shop_name'));
        }

        try {
            $promotionModel->startTrans();

            if (empty($post['promotion_id'])) {
                $count = $promotionModel->getCount(['combination_name' => $post['combination_name'], 'is_delete' => 1]);
                if ($count > 0) {
                    return json(msg(400, '', '组合名称已存再，请勿重复添加'));
                }
                $promotion_id = $promotionModel->getAddDataId($pro_arr);

                if (!$promotion_id) {
                    throw  new \think\Exception('促销活动添加失败');
                }
            } else {
                $find = $promotionModel->getFind(['combination_name' => $post['combination_name']]);

                if (!empty($find) && $find['id'] != $post['promotion_id']) {
                    return json(msg(400, '', '组合名称已存再，请勿重复添加'));
                }
                unset($pro_arr['create_time']);
                unset($pro_arr['create_user_id']);

                $promotionModel->updates(['id' => $post['promotion_id']], $pro_arr);
                $combinedModel->delData(['promotion_id' => $post['promotion_id']]);
                $promotion_id = $post['promotion_id'];
            }
//            $post['combined'] = [
//                ['product_id'=>1,'promotion_price'=>1,'rebate'=>1,'config_number'=>1,'combination_type'=>1],
//                ['product_id'=>2,'promotion_price'=>2,'rebate'=>2,'config_number'=>1,'combination_type'=>1],
//                ['product_id'=>3,'promotion_price'=>3,'rebate'=>3,'config_number'=>1,'combination_type'=>1],
//            ];


            foreach ($post['combined'] as $k => $v) {
                $task_arr[] = [
                    'promotion_id' => $promotion_id,
                    'product_id' => $v['product_id'],
                    'promotion_price' => $v['promotion_price'],
                    'rebate' => $v['rebate'],
                    'config_number' => $v['config_number'],
                    'combination_type' => $v['combination_type'],
                    'common_price' => $v['common_price'] ?? '',
                ];
            }

            $add = $combinedModel->addAll($task_arr);
            if (!$add) {
                throw new \think\Exception('商品添加失败');
            }

            //智能消息-商品-山特-营销组合创建
            $message = new MessageModel();
            $group = Db::table('yf_admin_rights_group')->where('rights_group_name', '产品总监')->value('rights_group_id');
            $adminUser = Db::table('lncrm_auth_group_access')->where('group_id', $group)->column('uid');
            if($adminUser){
                foreach ($adminUser as $k => $v){
                    $message->message_send('新营销组合待审批', '商品', 1, $v, [$post['combination_name']], ['yf_sales_promotion', $post['combination_name']]);
                }
            }

            $promotionModel->commit();
            return json(msg(200, '', '成功'));
        } catch (\Exception $e) {
            $promotionModel->rollback();
            return json(msg(400, '', $e->getMessage()));
        }
    }
    #endregion

    #region促销审核
    public function promotionReview(PromotionModel $promotionModel)
    {
        $user_id = $this->user_id;
        //$shop_id = 2;  //yunyan_addmodi_20220211 超级用户可以审核管理记录
        $shop_id = $this->shop_id ?: 2;
        $post = input('post.');
        //yunyan_addmodi_20220211 超级用户可以审核管理记录
        if ($user_id == 10001) {
            $count = $promotionModel->getCount(['approval_status' => 1, 'is_delete' => 1, 'id' => $post['promotion_id'],]);
        } else {
            $count = $promotionModel->getCount(['approval_status' => 1, 'is_delete' => 1, 'id' => $post['promotion_id'], 'shop_id' => $shop_id]);
        }
        if ($count <= 0) {
            return json(msg(400, '', '暂无待审批促销')); 
        }

        $user_name = Db::table('lncrm_admin')->where(['id' => $user_id])->field('username')->find();
        $arr = [
            'approval_status' => $post['approval_status'],//审核状态 2-通过 3-拒绝
            'approval_remarks' => $post['approval_remarks'] ?? '',//审核备注
            'approval_time' => date('Y-m-d H:i:s'),//审核时间
            'approver' => $user_name['username'],//审核人,
            'approver_id' => $user_id
        ];
        if ($post['approval_status'] == 3 && empty($post['approval_remarks'])) {
            return json(msg(400, '', '【拒绝】必须要填写理由'));
        }
//yunyan_addmodi_20220211 超级用户可以审核管理记录
        if ($user_id == 10001) {
            $edit = $promotionModel->updates(['approval_status' => 1, 'is_delete' => 1, 'id' => $post['promotion_id'],], $arr);
        } else {
            $edit = $promotionModel->updates(['approval_status' => 1, 'is_delete' => 1, 'id' => $post['promotion_id'], 'shop_id' => $shop_id], $arr);
        }

        //智能消息-商品-山特-营销组合审批 approval_status 1-待审批   2-审批通过 3-审批拒绝'
        if($post['approval_status'] === 2 || $post['approval_status'] === 3){
            if($post['approval_status'] === 2){
                $name = '营销组合审批通过';
            }else{
                $name = '营销组合审批不通过';
            }
            $message = new \app\model\message\MessageModel();
            $pro = $promotionModel->getFind(['id' => $post['promotion_id']]);
            $params = [$pro['combination_name']];
            if(isset($post['approval_remarks'])){
                $params[] = '备注：'.$post['approval_remarks'];
            }else{
                $params[] = '';
            }
            $message->message_send($name, '商品', 1, $pro['create_user_id'], $params, ['yf_sales_promotion', $post['promotion_id']]);
        }
        

        //审批完设置发布状态为1未发布，产品经理手动上架 release_status=1 yunyan_modi_20220126
        if ($edit !== false) {
            return json(msg(200, '', '成功'));
        } else {
            return json(msg(400, '', '失败'));
        }
    }
    #endregion

    #region促销详情
    public function promotionDetails(PromotionModel $promotionModel, TaskModel $taskModel, CombinedModel $combinedModel, RulesModel $rulesModel)
    {
        $promotion_id = input('get.promotion_id');
        $promotion = $promotionModel->getFind(['id' => $promotion_id]);
        // 重置promotionName 由于存储过程中有错误
        if (empty($promotion)) {
            return json(msg(400, '', '促销编号错误'));
        }
        if ($promotion['activity_type'] == 1)//组合促销
        {
            $district = Db::table('yf_shop_base')->alias('a')
                ->join('yf_base_district b', 'a.district_id_p1=b.district_id')
                ->join('yf_shop_grade c', 'c.shop_grade_id=a.shop_grade_id')
                ->whereIn('a.shop_id', $promotion['participants_id'])->field('a.district_id_p1,b.district_name')
                ->field('a.district_id_p1,b.district_name,a.shop_grade_id,c.shop_grade_name')
                ->select()->toArray();

            $promotion['district_id'] = implode(',', array_column($district, 'district_id_p1'));//地区id
            $promotion['district_name'] = implode(',', array_column($district, 'district_name'));//地区名称
            $promotion['shop_grade_id'] = implode(',', array_unique(array_column($district, 'shop_grade_id')));//级别id

            $shop_grade_name = array_column($district, 'shop_grade_name');
            foreach ($shop_grade_name as $k => $v) {
                if ($v == 'T0') {
                    $shop_grade_name[$k] = '山特';
                }
            }
            $promotion['shop_grade_name'] = implode(',', array_unique($shop_grade_name));//级别名称

            $combined = $combinedModel->getCombinedPromotion($promotion_id);
            $promotions = Db::table('yf_combined_promotion')->where(['promotion_id' => $promotion_id])->distinct(true)->field("combination_type")->select()->toArray();

            foreach ($promotions as $k => $v) {
                $promotions[$k]['goods'] = [];
            }

            foreach ($promotions as $k => $v) {
                foreach ($combined as $key => $value) {
                    if ($value['combination_type'] == $v['combination_type']) {
                        array_push($promotions[$k]['goods'], $value);
                    }
                }
            }
            $promotion['combined'] = $promotions;

        } else//指定任务促销
        {
            $promotion['combined'] = $taskModel->getTaskPromotion($promotion_id);
            $promotion['rule'] = $rulesModel->getAlls(['promotion_id' => $promotion_id], 'id,participants_id,mission_objectives,overall_proportion,individual_proportion,rebate_ratio,integral_proportion,participants_name');
        }
        $promotion['end_date'] = date('Y-m-d', strtotime($promotion['end_date']) - (60*60*24));
        return json(msg(200, $promotion, '成功'));
    }
    #endregion

    #region促销套餐列表
    public function promotionList(CombinedModel $combinedModel, PromotionModel $promotionModel, TaskModel $taskModel, RulesModel $rulesModel)
    {
        $get = input('get.');

        $user_id = 11505;
        $where = '';

        if ($get['type'] != 0)//类型  0-默认 1-组合套餐 2-指定任务套餐
        {
            $where .= " and activity_type = {$get['type']}";
        }

        if ($get['status'] == 0)//状态搜索 0-正在进行 1-即将开始
        {
            $where .= " and effective_date <= now()";
            $where .= " and end_date>= now()";
        } else {
            $where .= " and effective_date > now()";
        }
        $cwhere = '';
        $twhere = '';

        if (!empty($get['min_price']) && !empty($get['max_price'])) {
            $cwhere .= " and pr.promotion_price between {$get['min_price']} and {$get['max_price']}";
            $twhere .= " and goods.common_price between {$get['min_price']} and {$get['max_price']}";
        }

        $corder = '';
        $torder = '';
        if (!empty($get['sort']))//排序 1-价格 3-销售
        {
            switch ($get['sort']) {
                case 1:
                    $corder .= " order by pr.promotion_price desc";
                    $torder .= " order by goods.common_price desc";
                    break;
                case 3:
                    $corder .= " order by ba.goods_salenum desc";
                    $torder .= " order by ba.goods_salenum desc";
                    break;
            }
        }
        $shop_id = $this->shop_id;
        $sql = "select id,combination_name,activity_type,participants_id from yf_sales_promotion where is_delete=1 and approval_status=2 and ((activity_type=1 and FIND_IN_SET($shop_id,participants_id)) or (activity_type=2 and (select GROUP_CONCAT(participants_id) from yf_task_promotion_rules where FIND_IN_SET($shop_id,participants_id)))) $where";
        $promotion_arr = Db::query($sql);//促销活动

        if (empty($promotion_arr)) {
            return json(msg(200, [], '成功'));
        }

        $promotion_id = implode(',', array_column($promotion_arr, 'id'));
        $combined_arr = $combinedModel->getCombinedPromotions($cwhere, $corder, $promotion_id);//组合促销
        $tast_arr = $taskModel->getTaskPromotions($twhere, $torder, $promotion_id);//指定任务促销


        if ($get['type'] == 1) {
            $tast_arr = [];
        } elseif ($get['type'] == 2) {
            $combined_arr = [];
        }
        $sql = "select promotion_id,participants_id,mission_objectives,overall_proportion,individual_proportion,rebate_ratio,integral_proportion from yf_task_promotion_rules where FIND_IN_SET($shop_id,participants_id)";
        $rules_arr = Db::query($sql);

        foreach ($promotion_arr as $k => $v) {
            $promotion_arr[$k]['promotion'] = [];
            $promotion_arr[$k]['rules'] = [];
            foreach ($combined_arr as $key => $value) {
                $value['common_name'] = $value['common_name'] ?? '';
                $value['pino'] = $value['pino'] ?? '';
                $value['promotion_price'] = $value['promotion_price'] ?? 0.00;
                $value['common_image'] = $value['common_image'] ?? '';
                $value['common_property'] = $value['common_property'] ?? '';
                $value['config_number'] = $value['config_number'];
                if ($value['promotion_id'] == $v['id']) {
                    array_push($promotion_arr[$k]['promotion'], $value);//追加组合促销
                }
            }
            foreach ($tast_arr as $key => $value) {
                $value['common_name'] = $value['common_name'] ?? '';
                $value['pino'] = $value['pino'] ?? '';
                $value['promotion_price'] = $value['promotion_price'] ?? 0.00;
                $value['common_image'] = $value['common_image'] ?? '';
                $value['common_property'] = $value['common_property'] ?? '';
                $value['goods_cat_nav_name'] = $value['goods_cat_nav_name'] ?? '';
                $value['nav_name'] = $value['nav_name'] ?? '';
                if ($value['promotion_id'] == $v['id']) {
                    array_push($promotion_arr[$k]['promotion'], $value);//追加制定任务促销
                }
            }

            foreach ($rules_arr as $key => $value) {
                if ($value['promotion_id'] == $v['id']) {

                    $value['no_complete'] = $value['mission_objectives'] - $value['mission_objectives'] * $value['individual_proportion'];//未完成任务金额
                    $value['get_rebate'] = $value['mission_objectives'] * $value['rebate_ratio'];//可获得返利
                    $value['get_integral'] = $value['mission_objectives'] * $value['integral_proportion'];//可获得积分

                    array_push($promotion_arr[$k]['rules'], $value);//追加指定任务促销规则
                }
            }
        }

        return json(msg(200, $promotion_arr, '成功'));
    }
    #endregion

    #region促销列表
    public function getPromotion(CombinedModel $combinedModel)
    {
        $group = Db::table('lncrm_auth_group_access')->where('uid',$this->user_id)->find();
        $group = $group ? Db::table('yf_admin_rights_group')->where('rights_group_id', $group['group_id'])->value('rights_group_name') : false;
        $group = trim($group);
        $agent = null;
        if ($group == '区域销售' || $group == 'Sell-in销售') {
            $area = Db::table('lncrm_cms_diyform_quyushenpijuese')->where('sales_user_id', $this->user_id)->column('suoshuquyu');
            if ($area) {
                $area  = implode('', $area);
                $area  = provinceAbbrConversion($area);
                $where = [];
                foreach ($area as $value) {
                    $where[] = ['usecity', 'like', "$value%"];
                }
                $agent = Db::table('lncrm_cms_diyform_dailishang')->whereOr($where)->column('companyname');
                if (!$agent) return json(msg(200, [], '成功'));

                $agent = array_unique($agent);
                $where = [];
                foreach ($agent as $value) {
                    $where[] = ['participants_name', 'like', "%$value%"];
                }
                $agent = Db::table('yf_sales_promotion')->whereOr($where)->column('id');
                if (!$agent) return json(msg(200, [], '成功'));
                $agent = implode(',', $agent);
            }
        }
//        var_dump(Db::table('yf_shop_base')->limit(1)->select());

        $get = input('get.');
        $limit = $get['pageSize'];
        $offset = ($get['pageNumber'] - 1) * $limit;
        $user_id = $this->user_id;
        //$shop_id = 2;  //yunyan_addmodi_20220211 超级用户可以审核管理记录
//        $shop_id = $this->shop_id ?: $shop ?: 2;
        $shop_id = $this->shop_id ?: 2;
        if ($user_id == 10001) {
            $shop_id = 0;
        }
//        $shop_id = 0;
        $data = $combinedModel->getCombined($get, $limit, $offset, $shop_id, $agent);
        return json(msg(200, $data, '成功'));
    }
    #endregion

    #region首页促销规则待审核数量
    public function getHomePromotions(CombinedModel $combinedModel)
    {
        $get = input('get.');
        $get['approval_status'] = 1;
       // $limit = $get['pageSize'];
       // $offset = ($get['pageNumber'] - 1) * $limit;

        $user_id = $this->user_id;
        //$shop_id = 2;  //yunyan_addmodi_20220211 超级用户可以审核管理记录
        $shop_id = $this->shop_id ?: 2;
        if ($user_id == 10001) {
            $shop_id = 0;
        }
        $date = date('Y-m-d');
        // 不需要更新为拒绝状态 2023-0427 梁荣泽
//        Db::table('yf_sales_promotion')->where('end_date', '<=', $date)->update(['approval_status' => 3]);
//        $rights_group_rights_ids = Db::table('lncrm_admin')->alias('a')
//            ->join('yf_admin_rights_group b','b.rights_group_id=a.groupid','left')
//            ->where(['a.id'=>$user_id,'a.spid'=>$shop_id])->field('rights_group_rights_ids')->find();
        //超级用户admin获取所有数据，用于审核管理 yunyan_add_20220211
        if ($user_id == 10001) {
            $rights_group_rights_ids = Db::table('lncrm_auth_group_access')->alias('a')
                ->join('yf_admin_rights_group b', 'a.group_id=b.rights_group_id', 'left')
                ->field('a.group_id,b.rights_group_rights_ids')->select()->toArray();
        } else {
            $rights_group_rights_ids = Db::table('lncrm_auth_group_access')->alias('a')
                ->join('yf_admin_rights_group b', 'a.group_id=b.rights_group_id', 'left')
                ->where(['a.uid' => $user_id])->field('a.group_id,b.rights_group_rights_ids')->select()->toArray();
        }

        $str = [];
        foreach ($rights_group_rights_ids as $k => $v) {
            $strlen = strlen($v['rights_group_rights_ids']);
            $strs = mb_substr($v['rights_group_rights_ids'], 1, $strlen - 2) . ',0';
            $strs = explode(',', $strs);
            foreach ($strs as $ks => $vs) {
                array_push($str, $vs);
            }
        }

        if (!in_array('19875', $str) && !in_array('19841', $str)) {
            return json(msg(200, [], '暂无数据'));
        }

        $data = $combinedModel->getHomeCombined($get, 0, 0, $shop_id);
       // $data['curr_userId'] = $user_id;//用于前端页面判断  yunyan_add_20211228
        return json(['count'=>$data['count']]);
    }

    #region促销规则列表
    public function getPromotions(CombinedModel $combinedModel)
    {
        $get = input('get.');
        $limit = $get['pageSize'];
        $offset = ($get['pageNumber'] - 1) * $limit;

        $user_id = $this->user_id;
        //$shop_id = 2;  //yunyan_addmodi_20220211 超级用户可以审核管理记录
        $shop_id = $this->shop_id ?: 2;
        if ($user_id == 10001) {
            $shop_id = 0;
        }
        $date = date('Y-m-d');
        // 不需要更新为拒绝状态 2023-0427 梁荣泽
//        Db::table('yf_sales_promotion')->where('end_date', '<=', $date)->update(['approval_status' => 3]);
//        $rights_group_rights_ids = Db::table('lncrm_admin')->alias('a')
//            ->join('yf_admin_rights_group b','b.rights_group_id=a.groupid','left')
//            ->where(['a.id'=>$user_id,'a.spid'=>$shop_id])->field('rights_group_rights_ids')->find();
        //超级用户admin获取所有数据，用于审核管理 yunyan_add_20220211
        if ($user_id == 10001) {
            $rights_group_rights_ids = Db::table('lncrm_auth_group_access')->alias('a')
                ->join('yf_admin_rights_group b', 'a.group_id=b.rights_group_id', 'left')
                ->field('a.group_id,b.rights_group_rights_ids')->select()->toArray();
        } else {
            $rights_group_rights_ids = Db::table('lncrm_auth_group_access')->alias('a')
                ->join('yf_admin_rights_group b', 'a.group_id=b.rights_group_id', 'left')
                ->where(['a.uid' => $user_id])->field('a.group_id,b.rights_group_rights_ids')->select()->toArray();
        }

        $str = [];
        foreach ($rights_group_rights_ids as $k => $v) {
            $strlen = strlen($v['rights_group_rights_ids']);
            $strs = mb_substr($v['rights_group_rights_ids'], 1, $strlen - 2) . ',0';
            $strs = explode(',', $strs);
            foreach ($strs as $ks => $vs) {
                array_push($str, $vs);
            }
        }

        if (!in_array('19875', $str) && !in_array('19841', $str)) {
            return json(msg(200, [], '暂无数据'));
        }

        $data = $combinedModel->getCombined($get, $limit, $offset, $shop_id);
        $data['curr_userId'] = $user_id;//用于前端页面判断  yunyan_add_20211228
        return json(msg(200, $data, '成功'));
    }
    #endregion

    #region产品列表
    public function productLists()
    {
        $get = input('get.');
        $get['pageNumber'] = $get['pageNumber'] ?? 1;
        $limit = $get['pageSize'] ?? 10;

//        $shop_id = $this->shop_id;
        $shop_id = 2;
        $offset = ($get['pageNumber'] - 1) * $limit;
        $where = "where 1=1 and a.shop_id=$shop_id and a.common_state=1 and a.mpino is not null";
        if (!empty($get['common_name']))//产品型号
        {
            $where .= " and a.common_id = {$get['common_name']}";
        }
        if (!empty($get['pino']))//产品料号
        {
            $where .= " and a.mpino = '{$get['pino']}'";
        }
        if (!empty($get['listval']))//产品料号
        {
            @$balistval = base64_decode($get['listval']);

            if(!empty($balistval)){
                $rrs = explode(',',$balistval);
                $rrss = [];
                foreach($rrs as $rrsv){
                    if(strlen($rrsv)) $rrss[] = $rrsv;
                }
                $nbalistval = implode("','",$rrss);
            }
            if(strlen($nbalistval)>8)
            $where .= " and a.mpino not in ('".$nbalistval."')";
//            halt($where);
//            $where = str_replace('\t','',$where);
//            $where = str_replace('\r','',$where);
//            $where = str_replace('\n','',$where);
        }
        if (!empty($get['product_pl8']))//产品PL8
        {
            $where .= " and b.cat_id = '{$get['product_pl8']}'";
        }
        if (!empty($get['product_series']))//产品系列
        {
            $where .= " and d.cat_id = '{$get['product_series']}'";
        }
        if (!empty($get['common_id']))//产品
        {
            $where .= " and a.common_id = '{$get['common_id']}'";
        }
        if (!empty($get['cat_id'])) {
            $where .= " and a.cat_id = {$get['cat_id']}";
        }

        $sql = "select a.common_id,ifnull(a.common_name,'')as common_name,ifnull(a.mpino,'')as pino,ifnull(b.cat_name,'') as cat_name,ifnull(d.cat_name,'') as product_series,ifnull(a.common_name,'') as kname,ifnull(d.cat_parent_id,0) as pl8_cart_id,ifnull(a.cat_id,0) as cat_id
from yf_goods_common a 
		left join yf_goods_cat d on d.cat_id=a.cat_id
		left join yf_goods_cat b on b.cat_id=d.cat_parent_id
		$where
		GROUP BY a.common_id order by a.common_id desc  limit $offset,$limit";
        $data['datas'] = Db::query($sql);


        foreach ($data['datas'] as $k => $v) {
//            $price = Db::table('yf_goods_pricelistreport')->field('price')->where('pino', $v['pino'])->find();
            $price = null;
            if (!$price) {
                $num = Db::table('yf_goodsfield')->field('vd')->where('pino', $v['pino'])->where('kname', 'like', '%T1 价格对应数量%')->find();//标准数量

                $num['vd'] = $num['vd'] ?? '';
                $num['vd'] = str_replace(',', '', $num['vd']);
                $pwhere = " kname = '标准价格T1' AND pino = '{$v['pino']}'";
                $price = Db::table('yf_goodsfield')->field('vd')->where($pwhere)->find();//标准价格
                if ($num && $price) {
                    if(!empty($price['vd']) && !empty($num['vd'])){
                        $data['datas'][$k]['common_price'] = round($price['vd'] / $num['vd'], 2);
                    }else{
                        $data['datas'][$k]['common_price'] = 0;
                    }

                } else {
                    $num = Db::table('yf_goodsfield')->field('vd')->where('pino', $v['pino'])->where('kname', 'like', '%T2价格对应数量%')->find();//标准数量
                    $num['vd'] = $num['vd'] ?? '';

                    $num['vd'] = str_replace(',', '', $num['vd']);
                    $pwhere = " kname = '标准价格T2' AND pino = '{$v['pino']}'";
                    $price = Db::table('yf_goodsfield')->field('vd')->where($pwhere)->find();//标准价格
                    if ($num && $price) {
                        if(!empty($price['vd']) && !empty($num['vd'])){
                            $data['datas'][$k]['common_price'] = round($price['vd'] / $num['vd'], 2);
                        }else{
                            $data['datas'][$k]['common_price'] = 0;
                        }
//                        $data['datas'][$k]['common_price'] = round($price['vd'] / $num['vd'], 2);
                    } else {
                        $data['datas'][$k]['common_price'] = '暂无价格';
                    }
                }
            } else {
                $data['datas'][$k]['common_price'] = $price['price'];
            }

            $data['datas'][$k]['cat_id'] = intval($v['cat_id']);
        }
        foreach ($data['datas'] as $k => $v) {
            if ($v['common_price'] != '暂无价格') {
                $data['datas'][$k]['common_price'] = round($v['common_price'], 2);



            }
            $data['datas'][$k]['common_name'] = strip_tags( $data['datas'][$k]['common_name']);
            $data['datas'][$k]['common_name'] = str_replace('&amp;','>', $data['datas'][$k]['common_name']);
            $data['datas'][$k]['common_name'] = str_replace('amp;','', $data['datas'][$k]['common_name']);
            $data['datas'][$k]['kname'] =$data['datas'][$k]['common_name'];
        }
        $count = Db::query("select count(*)as count
from yf_goods_common a 
		left join yf_goods_cat d on d.cat_id=a.cat_id
		left join yf_goods_cat b on b.cat_id=d.cat_parent_id
		$where");
        $data['count'] = $count[0]['count'];
        return json(msg(200, $data, '成功'));
    }
    #endregion

    #region产品下拉选择列表
    public function productListSelect()
    {
        $get = input('get.');
        $get['pageNumber'] = $get['pageNumber'] ?? 1;
        $limit = $get['pageSize'] ?? 10;
        $offset = ($get['pageNumber'] - 1) * $limit;

        $where = 'where 1=1';
        if (!empty($get['common_name']))//产品型号
        {
            $where .= " and a.common_name like '%{$get['common_name']}%'";
        }
        if (!empty($get['cat_id']))//产品系列id
        {
            $where .= " and a.cat_id = {$get['cat_id']}";
        }
        $sql = "select a.common_id,a.common_name,a.cat_name,a.cat_id as cat_id,c.cat_parent_id as cat_parent_id
    from yf_goods_common a
    left join yf_goods_cat c on a.cat_id=c.cat_id $where GROUP BY a.common_id order by a.common_id  desc   limit $offset,$limit";

        $data['datas'] = Db::query($sql);

        $count = Db::query("select count(*)as count
    from yf_goods_common a  $where and  a.common_state=1");
        $data['count'] = $count[0]['count'];
        return json(msg(200, $data, '成功'));
    }
    #endregion

    #region促销删除
    public function promotionDeletion(PromotionModel $promotionModel)
    {
        $promotion_id = input('get.promotion_id');
        $count = $promotionModel->getCount(['id' => $promotion_id, 'is_delete' => 1]);
        if ($count <= 0) {
            return json(msg(400, '', '该促销活动已被删除'));
        }
        $arr = [
            'is_delete' => 2,
            'update_time' => date('Y-m-d H:i:s'),
            'update_user_id' => $this->user_id
        ];
        $data = $promotionModel->updates(['id' => $promotion_id, 'is_delete' => 1], $arr);
        if ($data) {
            return json(msg(200, '', '成功'));
        } else {
            return json(msg(400, '', '失败'));
        }
    }
    #endregion

    #region组合类别列表
    public function getCategory()
    {
        $data = Db::table('yf_combined_promotion')->distinct(true)->column("combination_type");
        return json(msg(200, $data, '成功'));
    }
    #endregion

    #region查看管理员下面的所有经销商
    public function getShopBase(ShopBaseModel $shopBaseModel)
    {
        $shop_id = $this->shop_id;
        $data = $shopBaseModel->nativeQuery($shop_id);
        return json(msg(200, $data, '成功'));
    }
    #endregion

    #region获取料号列表
    public function getPino()
    {
        $data = Db::table('yf_goods_common')->where(['common_state' => 1])->where('mpino', 'not null')->distinct(true)->field('mpino')->select()->toArray();
        $pino = array_column($data, 'mpino');
        return json(msg(200, $pino, '成功'));
    }
    #endregion

    #region获取产品料号列表
    public function getProductPino()
    {
        $cat_id = input('get.cat_id');
        $pino = input('get.pino');
        $where = '1=1';
        if (!empty($cat_id)) {
            $where .= " and a.cat_id=$cat_id";
        }
        if (!empty($pino)) {
            $where .= " and a.mpino like '%{$pino}%'";
        }
        $shop_id = 2;

        $data = Db::table('yf_goods_common')->alias('a')
            ->join('yf_goods_cat b', 'a.cat_id=b.cat_id', 'left')
            ->join('yf_goods_cat c', 'c.cat_id=b.cat_parent_id', 'left')
            ->where(['a.common_state' => 1, 'a.shop_id' => $shop_id])
            ->where('a.mpino', 'not null')
            ->where($where)
            ->field('a.common_id,a.mpino as pino,a.cat_id,b.cat_name,b.cat_parent_id,c.cat_name as cat_parent_name')
            ->select();
        return json(msg(200, $data, '成功'));
    }
    #endregion

    #region获取产品型号列表
    public function getProductModel()
    {
        $cat_id = input('get.cat_id');
        $name = input('get.name');
        $where = '1=1';
        if (!empty($cat_id)) {
            $where .= " and a.cat_id=$cat_id";
        }
        if (!empty($name)) {
            $where .= " and a.common_name like '%{$name}%'";
        }
        $shop_id = 2;

        $data = Db::table('yf_goods_common')->alias('a')
            ->join('yf_goods_cat b', 'a.cat_id=b.cat_id', 'left')
            ->join('yf_goods_cat c', 'c.cat_id=b.cat_parent_id', 'left')
            ->where(['a.common_state' => 1, 'a.shop_id' => $shop_id])
            ->where('a.mpino', 'not null')
            ->where($where)
            ->field('a.common_id,a.common_name,a.mpino as pino,a.cat_id,b.cat_name,b.cat_parent_id,c.cat_name as cat_parent_name')
            ->select();
        $data = $data->each(function ($item, $key) {
            $item['common_name'] = strip_tags( $item['common_name']);
            $item['common_name'] = str_replace('&amp;','>', $item['common_name']);
            $item['common_name'] = str_replace('amp;','', $item['common_name']);
           return $item;
        });
        return json(msg(200, $data, '成功'));
    }
    #endregion
}