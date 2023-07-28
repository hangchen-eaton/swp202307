<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>报备新商机</title>
    <link rel="stylesheet" href="<?=($this->view->js)?>/elementui-2.15.6/element-icons.ttf">
    <link rel="stylesheet" href="<?=($this->view->js)?>/elementui-2.15.6/element-icons.woff">
    <link rel="stylesheet" href="<?=($this->view->js)?>/elementui-2.15.6/icon.css">
    <link rel="stylesheet" href="<?=($this->view->js)?>/elementui-2.15.6/elmentui.css">
    <link rel="stylesheet" href="<?=($this->view->js)?>/common.css">
    <link rel="stylesheet" href="<?=($this->view->js)?>/scss/addBusiness.css">
    <script src='<?=($this->view->js)?>/vue-2.6.12.js'></script>
    <script src='<?=($this->view->js)?>/elementui-2.15.6/elementui.js'></script>
    <script src="<?=($this->view->js)?>/script/axios.min.js" type="text/javascript" charset="utf-8"></script>
    <script src="<?=($this->view->js)?>/script/config.js" type="text/javascript" charset="utf-8"></script>
    <script src="/shop_admin/static//default/js/script/apiData.js" type="text/javascript" charset="utf-8"></script>
    <script src="/shop_admin/static/default/js/apiData.js" type="text/javascript" charset="utf-8"></script>
    <script src="https://cdn.bootcss.com/moment.js/2.20.1/moment.js"></script>
    <style>
        .my-autocomplete {
        li {
            line-height: normal;
            padding: 7px;

        .name {
            text-overflow: ellipsis;
            overflow: hidden;
        }
        .addr {
            font-size: 12px;
            color: #b4b4b4;
        }

        .highlighted .addr {
            color: #ddd;
        }
        }
        }
    </style>
</head>
<body>
<div class="new-business-box" id="new-business-box">
    <div class="form-box">
        <!-- 报备基本信息 -->
        <div class="common-title">商机基本信息</div>
        <el-form ref="basicForm" :inline="true" :model="basicForm" :rules="basicFormRule" size="small">
            <input type="hidden" id="id" value="<?php if(!empty($pid)){echo $pid;}?>" />
            <el-row :gutter="20">
                <el-col :span="8">
                    <el-form-item label="项目来源:" prop='basicB'>
                        <el-select disabled v-model="basicForm.basicB" placeholder="请选择">
                            <el-option v-for="item in areaList14" :key="item.id" :label="item.title"
                                       :value="item.id">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="项目覆盖范围:" prop='basicC'>
                        <el-select :disabled="id?true:false" v-model="basicForm.basicC" placeholder="请选择" @change="changeRange">
                            <el-option v-for="item in areaList" :key="item.value" :label="item.label" :value="item.value">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="MOEM设备配套:" prop='basicE'>
                        <el-select :disabled="id?true:false" v-model="basicForm.basicE" placeholder="请选择" style="width:50%">
                            <el-option v-for="item in areaList1" :key="item.value" :label="item.label" :value="item.value"> </el-option>
                        </el-select>
                        <el-tooltip content="UPS主要应用于智能设备和生产制造设备中的情况以及UPS 主要应用于行业相关配套应用的项目中的情况，均属于MOEM设备配套" placement="top" effect="light">
                            <i class="el-icon-question" style="color: #dfc2c2;font-size: 18px"></i>
                        </el-tooltip>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row :gutter="20">
                <el-col :span="24">
                    <el-form-item label="项目名称:" prop='basicA_val'>
                        <div style="display:flex;width:100%">
                            <el-input v-if="basicForm.basicC===2 && userForm.userInfoF" :disabled="id?true:false" v-model="basicForm.basicA_city" placeholder="选择地区自动带出" style="width:200px;margin-right:15px"></el-input>
                            <el-input style="flex:1" type="textarea" :disabled="id?true:false" value="232132" v-model="basicForm.basicA_val" placeholder=""></el-input>
                        </div>
                    </el-form-item>
                </el-col>

            </el-row>
            <el-row :gutter="20">
                <el-col :span="8">
                    <el-form-item label="客户行业:" prop='basicF'>
                        <el-select :disabled="id?true:false" v-model="basicForm.basicF" placeholder="请选择">
                            <el-option v-for="item in areaList2" :key="item.industry_id" :label="item.industry_name" :value="item.industry_id"> </el-option>
                        </el-select>
                        <div class="form-item-tip">{{basicFDesc}}</div>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="集成商:" prop='basicG'>
                        <el-input :disabled="id?true:false" v-model="basicForm.basicG" placeholder=""></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="集成商省份:" prop='basicH'>
                        <el-select :disabled="id?true:false" v-model="basicForm.basicH" filterable placeholder="请选择">
                            <el-option :value="0"  label="无"></el-option>
                            <el-option v-for="item in provinceList" :key="item.district_id" :label="item.district_name"
                                       :value="item.district_id">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>


            </el-row>
            <el-row :gutter="20">
                <el-col :span="8">
                    <el-form-item label="赢单率:" prop='basicI'>
                        <el-select v-model="basicForm.basicI" placeholder="请选择" :disabled="id?true:false" >
                            <el-option v-for="item in areaList4" :key="item.id" :label="item.percentage"
                                       :value="item.id">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="预计出货日期:" prop='basicN' v-if="basicForm.basicC===2">
                        <el-date-picker :disabled="id?true:false"
                                v-model="basicForm.basicN"
                                type="date"
                                :editable="false"
                                placeholder="预计出货日期"
                                value-format="yyyy-MM-dd"
                        ></el-date-picker>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="投标日期:" prop='basicJ'>
                        <el-date-picker :disabled="id?true:false"
                                v-model="basicForm.basicJ"
                                type="date"
                                :editable="false"
                                placeholder="投标日期"
                                value-format="yyyy-MM-dd"
                        ></el-date-picker>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row :gutter="20">
                <el-col :span="8">
                    <el-form-item label="招标形式:" prop='basicK'>
                        <el-select :disabled="id?true:false" v-model="basicForm.basicK" placeholder="请选择">
                            <el-option v-for="item in areaList11" :key="item.value" :label="item.label"
                                       :value="item.value">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="是否需要授权书:" prop='basicL'>
                        <el-select :disabled="id?true:false" v-model="basicForm.basicL" placeholder="请选择">
                            <el-option v-for="item in areaList1" :key="item.value" :label="item.label"
                                       :value="item.value">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="有效期至:" prop='basicM'>
                        <el-date-picker :disabled="id?true:false" v-model="basicForm.basicM"
                                        type="date"
                                        :editable="false"
                                        placeholder="有效期至"
                                        value-format="yyyy-MM-dd"

                        ></el-date-picker>
                    </el-form-item>
                </el-col>
            </el-row>
        </el-form>
        <div class="common-title">最终用户信息</div>
        <el-form ref="userForm" :inline="true" :model="userForm" :rules="userFormRule" size="small">
            <el-row :gutter="20">
                <el-col :span="8">
                    <el-form-item label="最终用户名称:" prop='userInfoA'>
                        <el-select :disabled="id?true:false"
                                v-model="userForm.userInfoA"
                                filterable
                                remote
                                reserve-keyword
                                :remote-method="userInfoASearch"
                                @change="userInfoAChange"
                                :loading="userInfoALoading">
                            <el-option
                                    v-for="item in userInfoAOptions"
                                    :key="item.id"
                                    :label="item.endUser"
                                    :value="item.endUser">
                            </el-option>
                            <el-option v-if="userInfoASearchValue" :label="userInfoASearchValue" :value="userInfoASearchValue"></el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="联系人姓名:" prop='userInfoB'>
                        <el-input :disabled="id?true:false" v-model="userForm.userInfoB"   placeholder=""></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="电话/手机:" prop='userInfoC'>
                        <el-input :disabled="id?true:false" v-model="userForm.userInfoC" placeholder=""></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row :gutter='20' v-if='basicForm.basicC===2'>
                <el-col :span="8">
                    <el-form-item label="国家:" prop='userInfoE'>
                        <el-input :disabled="id?true:false" v-model="userForm.userInfoE" placeholder="中国"></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="省份:" prop='userInfoF'>
                        <el-select :disabled="id?true:false"
                                   @change="changeProvince"
                                   v-model="userForm.userInfoF"
                                   placeholder="">
                            <el-option v-for="item in provinceList" :label="item.district_name" :value="item.district_id"></el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="市:" prop='userInfoG'>
                        <el-select :disabled="id?true:false"
                                   @change="changeCity"
                                   v-model="userForm.userInfoG" placeholder="">
                            <el-option v-for="item in cityList" :label="item.district_name" :value="item.district_id"></el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row :gutter='20'>
                <el-col :span="8" v-if='basicForm.basicC===2'>
                    <el-form-item label="地址:" prop='userInfoH'>
                        <el-input :disabled="id?true:false" v-model="userForm.userInfoH" placeholder=""></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="8" v-if='basicForm.basicC===1'>
                    <el-form-item label="区域:" prop='userInfoD'>
                        <el-select :disabled="id?true:false"
                                   v-model="userForm.userInfoD"
                                   multiple
                                   placeholder="请选择">
                            <el-option v-for="item in areaList8" :key="item.district_region" :label="item.district_region"
                                       :value="item.district_region">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="邮箱:" prop='userInfoI'>
                        <el-input :disabled="id?true:false" v-model="userForm.userInfoI" placeholder=""></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
        </el-form>
        <div class="common-title">投标单位</div>
        <el-form ref="tendererForm" :inline="true" :rules="tendererFormRule" :model="tendererForm" size="small">
            <el-row :gutter="20">
                <el-col :span="8">
                    <el-form-item label="公司名称:" prop='companyName'>
                        <el-select :disabled="id?true:false" v-model="tendererForm.companyName"
                                   filterable
                                   remote
                                   reserve-keyword
                                   :remote-method="companyNameSearch"
                                   @change="handleCompany"
                                   placeholder="请选择">
                            <el-option v-for="item in areaList13" :key="item.id" :label="item.company_name"
                                       :value="item">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="地址:" prop='address'>
                        <el-input :disabled="id?true:false" v-model="tendererForm.address" placeholder=""></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="联系人:" prop='user'>
                        <el-input :disabled="id?true:false" v-model="tendererForm.user" placeholder=""></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row :gutter="20">
                <el-col :span="8">
                    <el-form-item label="电话:" prop='phone'>
                        <el-input :disabled="id?true:false" v-model="tendererForm.phone" placeholder=""></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="邮箱:" prop='email'>
                        <el-input :disabled="id?true:false" v-model="tendererForm.email" placeholder=""></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="手机:" prop='telephone'>
                        <el-input :disabled="id?true:false" v-model="tendererForm.telephone" placeholder=""></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
        </el-form>
        <div class="common-title">招标单位</div>
        <el-form ref="tendeInvitForm" :inline="true" :rules="tendeInvitFormRule" :model="tendeInvitForm" size="small">
            <el-row :gutter="20">
                <el-col :span="8">
                    <el-form-item label="公司名称:" prop='companyName'>
                        <el-select :disabled="id?true:false"
                                   filterable
                                   remote
                                   reserve-keyword
                                   :remote-method="companyNameSearch2"
                                   @change="handleCompany2"
                                   v-model="tendeInvitForm.companyName"
                                   placeholder="请选择"
                        >
                            <el-option v-for="item in areaList9" :key="item.id" :label="item.company_name" :value="item">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="地址:" prop='address'>
                        <el-input :disabled="id?true:false" v-model="tendeInvitForm.address" placeholder=""></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="联系人:" prop='user'>
                        <el-input :disabled="id?true:false" v-model="tendeInvitForm.user" placeholder=""></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row :gutter="20">
                <el-col :span="8">
                    <el-form-item label="电话:" prop='phone'>
                        <el-input :disabled="id?true:false" v-model="tendeInvitForm.phone" placeholder=""></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="邮箱:" prop='email'>
                        <el-input :disabled="id?true:false" v-model="tendeInvitForm.email" placeholder=""></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="手机:" prop='telephone'>
                        <el-input :disabled="id?true:false" v-model="tendeInvitForm.telephone" placeholder=""></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
        </el-form>
        <div class="common-title">出货单位</div>
        <el-form ref="deliveryForm" :inline="true" :rules="deliveryFormRule" :model="deliveryForm" size="small">
            <el-row :gutter="20">
                <el-col :span="8">
                    <el-form-item label="公司名称:" prop='companyName'>
                        <el-select :disabled="id?true:false" v-model="deliveryForm.companyName" placeholder="请选择" @change="handleCh">
                            <el-option v-for="item in areaList5" :key="item.shop_id" :label="item.shop_name" :value="item">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="地址:" prop='address'>
                        <el-input disabled v-model="deliveryForm.address" placeholder=""></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="联系人:" prop='user'>
                        <el-input disabled v-model="deliveryForm.user" placeholder=""></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row :gutter="20">
                <el-col :span="8">
                    <el-form-item label="电话:" prop='phone'>
                        <el-input disabled v-model="deliveryForm.phone" placeholder=""></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="邮箱:" prop='email'>
                        <el-input disabled v-model="deliveryForm.email" placeholder=""></el-input>
                    </el-form-item>
                </el-col>
            </el-row>
        </el-form>
        <div class="common-title">商机支持产品</div>
        <el-form ref="productForm" :inline="true" :model="productForm" :rules='productFormRule' size="small">
            <el-row :gutter="20">
                <el-col :span="8">
                    <el-form-item label="产品线:" prop='productA'>
                        <el-select clearable v-model="productForm.productA" placeholder="请选择" @change="productChange">
                            <el-option v-for="item in areaList6" :key="item.cat_id" :label="item.cat_name" :value="item.cat_id"> </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="产品系列:" prop='productB'>
                        <el-select :no-data-text="'无'" clearable v-model="productForm.productB" placeholder="请选择" @change="productChange">
                            <el-option v-for="item in areaList7" :key="item.cat_id" :label="item.cat_name" :value="item.cat_id"> </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
<!--                    {{productForm.productF}}-->
                    <el-form-item label="产品型号:" prop='productF'>
                        <el-select v-model="productForm.productF" filterable
                                @change="productFChange"
                                :loading="productFLoading">
                            <el-option
                                    v-for="item in productFOptions"
                                    :key="item.common_id"
                                    :label="item.common_name"
                                    :value="item.common_id">
                            </el-option>
                            <!--
                            <div style="text-align: center">
                                <el-pagination
                                        small
                                        layout="prev,next"
                                        @current-change="handleProductChange"
                                        :current-page="productNumber"
                                        :page-size="productSize"
                                        :total="productTotal"
                                >
                                </el-pagination>
                            </div>
                            -->
                        </el-select>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-row :gutter="20">
                <el-col :span="8">
                    <el-form-item label="产品料号:" prop='productLIAOHAO'>
                        <el-select v-model="productForm.productLIAOHAO" filterable remote reserve-keyword :remote-method="productLIAOHAOSearch" @change="handleproductLIAOHAO" placeholder="请选择">
                            <el-option v-for="item in areaList15" :key="item.id" :label="item.pino" :value="item"> </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="预估数量:" prop='productE'>
                        <el-input v-model="productForm.productE" placeholder=""></el-input>
                    </el-form-item>
                </el-col>
            </el-row>

        </el-form>
        <div class="operate">
            <el-button type="primary" @click="add" size="small" :disabled="id?true:false" >加入</el-button>
        </div>
    </div>
    <div class="table-box">
        <div class="common-title">产品清单</div>
        <el-table :data="tableData">

            <template v-for="(item, index) in tableHeader">
                <el-table-column :prop="item.key" :label="item.label" v-if="item.key !== 'op'" :key="index"
                                 :min-width="item.minWidth" show-overflow-tooltip>
                    <template slot-scope="scope">
                            <span v-if="item.slot">
                                <el-input v-model="scope.row[item.key]" placeholder="" @change="computedPrice" :disabled="id?true:false"></el-input>
                            </span>
                        <span v-else>
                                {{ scope.row[item.key]}}
                            </span>
                    </template>
                </el-table-column>
                <el-table-column v-else label="操作" width="180">
                    <template slot-scope="scope">
                        <el-button type="text" size="small" :disabled="id?true:false"  @click="revoke(scope)">删除</el-button>
                    </template>
                </el-table-column>
            </template>
        </el-table>
        <div class="total">
            <el-form :inline="true">
                <el-form-item label="预计估值（元)" required>
                    <el-input type="number" style="width:220px;margin-right:15px;" v-model="total" placeholder="" :disabled="id?true:false"></el-input><span style="color:red;font-size:12px;" v-if="isShowTips">你选择的产品预估价值无法计算，请手动添加</span>
                </el-form-item>
            </el-form>
        </div>
        <div class="total" v-if="id">
            <el-form :inline="true">
            <el-form-item label="赢单状态:" >
                <el-select v-model="yd" placeholder="请选择">
                    <el-option  label="赢单"
                                value="7">
                    </el-option>
                    <el-option  label="丢单"
                                value="8">
                    </el-option>
                </el-select>
            </el-form-item>
            </el-form>
        </div>
    </div>


    <div class="bottom-box">
<!--        <div class="common-title">项目说明情况</div>-->
<!--        <el-input type="textarea" :rows="4" placeholder="请输入内容" v-model="basicForm.textarea">-->
<!--        </el-input>-->
        <div class="common-title">备注</div>
        <el-input type="textarea" :rows="4" placeholder="请输入内容" v-model="basicForm.textarea1">
        </el-input>
        <div class="operate">
            <el-button type="primary" v-if="!id" @click="onSubmit(1)" size="small">保存</el-button>
            <el-button v-if="mod_id || id" size="small" @click="closed()">取消</el-button>
            <el-button :disabled="pageLoading" type="primary" @click="onSubmit" size="small">提交</el-button>
        </div>
    </div>
</div>

<script>
    var activity_name = "<?=$activity_name;?>";
    var apply_user_name = "<?=$apply_user_name;?>";
    var apply_phone = "<?=$apply_phone;?>";
    var pid = "<?=$pid;?>";
    new Vue({
        el: '#new-business-box',
        data() {
            let checkPhone = (rule, value, callback) => {
                const phoneReg = /^1[3|4|5|7|8][0-9]{9}$/
                setTimeout(() => {
                    if (value) {
                        if (phoneReg.test(value)) {
                            if (!Number.isInteger(+value)) {
                                callback(new Error('请输入数字值'))
                            } else {
                                callback()
                            }
                        } else if(value.length == 12 && value.indexOf("-") != -1){
                            callback()
                        }else {
                            callback(new Error('电话号码格式不正确'))
                        }
                    }
                }, 100)
            }
            let checkEmail = (rule, value, callback) => {
                const mailReg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/
                setTimeout(() => {
                    if (value) {
                        if (mailReg.test(value)) {
                            callback()
                        } else {
                            callback(new Error('请输入正确的邮箱格式'))
                        }
                    }
                }, 100)
            }
            return {
                pickerOptions: {
                    disabledDate(time) {
                        console.log("999988");
                        /*
                        let times = Date.now() - 24 * 60 * 60 * 1000;
                        console.log('datatime',Date.now());
                        return time.getTime() < times;
                         */
                        return time.getTime() < Date.now() - 8.64e6;

                    }
                },
                useclear:0,
                yd:'',
                autoIno:1,
                id: null,
                mod_id: null,
                isBasicC: true,
                //报备基本信息
                basicForm: {
                    basicA: '',
                    basicA_val:activity_name,
                    basicA_city:'',
                    basicB: '',
                    basicC: 1,
                    // basicD: '',
                    basicE: '',
                    basicF: '',
                    basicG: '无',
                    basicH: 0,
                    basicI: '',
                    basicJ: '',
                    basicK: '',
                    basicL: '',
                    basicM: '',
                    basicN: '',
                    textarea: '',
                    textarea1: '',
                },
                basicFormRule: {
                    basicA_val: [{required: true, message: ' ', trigger: 'blur' }],
                    basicB: [{ required: true, message: '您不是销售人员，无法提交商机！', trigger: 'blur' }],
                    basicC: [{ required: true, message: ' ', trigger: 'blur' }],
                    // basicD: [{ required: false, message: ' ', trigger: 'blur' }],
                    basicE: [{ required: true, message: ' ', trigger: 'blur' }],
                    basicF: [{ required: true, message: ' ',  trigger: 'blur' }],
                    basicG: [{ required: true, message: ' ', trigger: 'blur' }],
                    basicH: [{ required: true, message: ' ', trigger: 'blur' }],
                    basicI: [{ required: true, message: ' ', trigger: 'blur' }],
                    basicN: [{ required: true, message: ' ', trigger: 'blur' }]
                },
                areaList1: [{
                    label: '是',
                    value: 1,
                }, {
                    label: '否',
                    value: 2
                }],
                areaList2: [],
                areaList4: [],
                areaList5: [],
                areaList6: [],
                areaList7: [],
                areaList8: [],
                areaList9: [],
                areaList10: [],
                areaList11: [
                    {
                        label: '公开招标',
                        value: '1',
                    },
                    {
                        label: '邀请招标',
                        value: '2'
                    },
                    {
                        label: '竞争性谈判',
                        value: '3'
                    },
                    {
                        label: '不经投标',
                        value: '4'
                    }
                ],
                areaList12: [{
                    label: '是',
                    value: 2,
                }, {
                    label: '否',
                    value: 1
                }],
                areaList13: [],
                areaList14: [],
                areaList: [
                    {
                        label: '区域性',
                        value: 2
                    },
                    {
                        label: '全国性',
                        value: 1,
                    },
                ],
                areaList15: [], // 产品料号
                //最终用户信息
                setCity: true,
                userForm: {
                    userInfoA: '',
                    userInfoB: apply_user_name,
                    userInfoC: apply_phone,
                    userInfoD: '',
                    userInfoE: '',
                    userInfoF: '',
                    userInfoG: '',
                    userInfoH: '',
                    userInfoI: '',
                },
                userInfoASearchValue: '',
                userFormRule: {
                    userInfoA: [{ required: true,message:' ', trigger: 'blur' }],
                    userInfoB: [{ required: true,message:' ', trigger: 'blur' }],
                    userInfoC: [{ required: true,message:' ', trigger: 'blur' }],
                    userInfoD: [{ required: true,message:' ', trigger: 'blur' }],
                    // userInfoE: [{ required: true,message:' ', trigger: 'blur' }],
                    userInfoF: [{ required: true,message:' ', trigger: 'blur' }],
                    userInfoG: [{ required: true,message:' ', trigger: 'blur' }],
                    // userInfoH: [{ required: true,message:' ', trigger: 'blur' }],
                    userInfoI: [{ required: false,validator: checkEmail, trigger: 'blur' }],
                },
                //投标单位
                tendererForm: {
                    companyName: '',
                    address: '',
                    user: '',
                    phone: '',
                    email: '',
                    telephone: ''
                },
                tendererFormRule: {
                    email: [{ required: false,validator: checkEmail, trigger: 'blur' }],
                    phone: [{ required: false,validator: checkPhone, trigger: 'blur' }],
                    telephone: [{ required: false,validator: checkPhone, trigger: 'blur' }],
                },
                //招标单位
                tendeInvitForm: {
                    companyName: '',
                    address: '',
                    user: '',
                    phone: '',
                    email: '',
                    telephone: ''
                },
                tendeInvitFormRule: {
                    phone: [{ required: false,validator: checkPhone, trigger: 'blur' }],
                    telephone: [{ required: false,validator: checkPhone, trigger: 'blur' }],
                    email: [{ required: false,validator: checkEmail, trigger: 'blur' }],
                },
                //出货单位
                deliveryForm: {
                    companyName: '',
                    address: '',
                    user: '',
                    phone: '',
                    email: '',
                },
                deliveryFormRule: {
                    email: [{ required: false,validator: checkEmail, trigger: 'blur' }],
                },
                //商机报备产品
                productForm: {
                    productA: '',
                    productB: '',
                    productC: '',
                    productD: '',
                    productE: '',
                    productF: '',
                    productLIAOHAO: '',
                },
                productFormRule: {
                    productA: [{ required: true, message: ' ', trigger: 'blur' }],
                    productB: [{ required: true, message: ' ', trigger: 'blur' }],
                    productE: [{ required: true, message: ' ', trigger: 'blur' }],
                    productF: [{ required: true, message: ' ', trigger: 'blur' }],
                    productLIAOHAO: [{ required: false, message: ' ', trigger: 'blur' }],
                },
                tableHeader: [
                    { label: '产品', key: 'productA', minWidth: '10%' },
                    { label: '料号', key: 'productB', minWidth: '10%' },
                    { label: '单价', key: 'productC', minWidth: '10%' },
                    { label: '数量', key: 'estimated_quantity', minWidth: '10%', slot: true },
                    { label: '操作', key: 'op', width: '180' }
                ],
                tableData: [],
                total: 0,
                nameCp : '',
                chanpindata :{},
                userInfoALoading: false,
                userInfoAOptions: [],
                productFLoading: false,
                productFBWork: false,
                productNumber: 1,
                productSize: 20,
                productTotal: 0,
                productSearch: '',
                productFOptions: [],
                provinceList: [],
                cityList: [],
                isShowTips: false,
                pageLoading: false,
                reg: /^\w+((.\w+)|(-\w+))@[A-Za-z0-9]+((.|-)[A-Za-z0-9]+).[A-Za-z0-9]+$/ //邮箱正则表达式
            }
        },
        computed:{
            basicFDesc(){
                let desc = ''
                this.areaList2.map(item=>{
                    if(item.industry_id===this.basicForm.basicF){
                        desc = item.industry_describe
                    }
                })
                return desc
            }
        },
        created() {
            this.id = this.getQueryString('id')
            this.mod_id = this.getQueryString('mod_id')
            if(this.id){
                this.useclear = -1;

            } else if(this.mod_id){
                this.useclear = -1;

            }else{
                setTimeout(()  =>{
                    this.dsat_out();
                    let timer = setInterval(() => {
                        this.dsat_in()
                    },1000);
                },1500);
            }


            this.hyList()
            this.winningRateList()
            this.getCh()
            this.goodsCatLists()
            this.getRegionLists()
            this.getProvinceList()
            this.getProjectSource()
            //默认获取全部产品字典
            //this.productFSearch()

            if(this.id){
                document.title = '更新商机'
                this.companyNameSearch()
                this.companyNameSearch2()
                this.getBusinessDetailAjax()
            }
            else if(this.mod_id){
                document.title = '编辑商机'
                this.companyNameSearch()
                this.companyNameSearch2()
                this.getBusinessDetailAjax()
            }
            else{
                this.getUserInformation()
                // this.isHangye()
                // this.isQudao()
                //默认有效期+180
                let now = new Date()
                now.setDate(now.getDate()+180)
                let setBasicM = now.getFullYear()+"-"+
                    (now.getMonth()>9?now.getMonth()+1:'0'+(now.getMonth()+1))+'-'+
                    (now.getDate()>9?now.getDate():'0'+now.getDate())
                this.basicForm.basicM = new Date(setBasicM)
                //是否山特人员 -出货单位
                // getPersonnel({}).then(res=>{
                //     if(res.data.shop_id){
                //         this.isBasicC = false
                //         this.deliveryForm.companyName = res.data.shop_id
                //         this.deliveryForm.address = res.data.companyaddress
                //         this.deliveryForm.user = res.data.c_realname
                //         this.deliveryForm.phone = res.data.companymobile
                //         this.deliveryForm.email = res.data.c_email
                //     }
                // })
            }
        },
        methods: {
            dsat_out(){
                let ax = [];
                try{
                    let s_garden_id = localStorage.getItem('s_garden_id') ? localStorage.getItem('s_garden_id') : '';
                     ax  = JSON.parse(s_garden_id)

                }catch (e) {
                    console.log("发生异常:" + e)
                }
                console.log('ax',ax);
                //ax.basicForm.basicH =parseInt(ax.basicForm.basicH);
                for(var zz in ax){
                    if(zz!='pageLoading')
                    if(zz!='userForm')
                        this.$data[zz] = ax[zz]
                    else{
                        if(ax[zz].userInfoA)
                            this.$data.userForm.userInfoA = ax[zz].userInfoA;
                        if(ax[zz].userInfoB)
                            this.$data.userForm.userInfoB = ax[zz].userInfoB;
                        if(ax[zz].userInfoC)
                            this.$data.userForm.userInfoC = ax[zz].userInfoC;
                        if(ax[zz].userInfoD)
                            this.$data.userForm.userInfoD = ax[zz].userInfoD;
                        if(ax[zz].userInfoE)
                            this.$data.userForm.userInfoE = ax[zz].userInfoE;
                        if(ax[zz].userInfoF)
                            this.$data.userForm.userInfoF = ax[zz].userInfoF;
                        if(ax[zz].userInfoG)
                            this.$data.userForm.userInfoG = ax[zz].userInfoG;
                        if(ax[zz].userInfoH)
                            this.$data.userForm.userInfoH = ax[zz].userInfoH;
                    }
                }

                console.log(12312312)
                // console.log(s_garden_id)
            },
            dsat_in(){
                if(this.autoIno==1)
                    localStorage.setItem('s_garden_id',JSON.stringify(this.$data))
                else
                    localStorage.setItem('s_garden_id',JSON.stringify({}))
                // console.log(JSON.stringify(this.$data))
                // console.log(localStorage.getItem('s_garden_id'))
            },
            changeRange() {
                this.basicForm.basicA_city = `${this.getProvinceName(this.userForm.userInfoF)}${this.getCityName(this.userForm.userInfoG)}`
            },
            productChange() {
                this.productFBWork = false
            },
            add() {
                this.$refs.productForm.validate((val)=>{
                    if(val){
                        let isIn = false
                        this.tableData.map(item=>{
                            if(item.product_id === this.chanpindata.common_id)
                            {
                                console.log(item)
                                console.log(this.chanpindata)
                                isIn = true
                                item.estimated_quantity = Number(item.estimated_quantity) + Number(this.productForm.productE)
                            }
                        })
                        if(!isIn)
                        {
                            this.tableData.push({
                                productA : this.chanpindata.common_name,
                                product_id : this.chanpindata.common_id,
                                product_line_id : this.productForm.productA, // 产品线
                                product_series_id : this.productForm.productB, // 产品系列
                                id: this.productForm.productA, // 产品线
                                estimated_quantity : this.productForm.productE,
                                productB: this.chanpindata.pino,
                                productC: this.chanpindata.common_price || this.chanpindata.price,
                            })
                        }
                        // 计算预估价值
                        this.computedPrice()
                    }else{
                        this.$message.error('请检查报备产品项中的必填项')
                    }
                })
            },
            // 计算预估价值
            computedPrice() {
                this.isShowTips= false
                const noPriceList = this.tableData.filter(item => item.productC === '暂无价格')
                if(noPriceList.length > 0) {
                    this.isShowTips= true
                    this.total = null
                    return
                }else {
                    const sum = this.tableData.reduce(function(prev, cur, index, arr) {
                        console.log(prev, cur, index);
                        return prev + cur.productC * cur.estimated_quantity;
                    },0)
                    this.total = Number(sum).toFixed(2)
                }

            },
            hyList(){
                const params = { //总的提交信息

                }
                getsSustomerIndustry(params).then(res => {
                    console.log(res)
                    this.areaList2 = res.data
                })
            },
            winningRateList(){
                const params = { //总的提交信息

                }
                winningRate(params).then(res => {
                    console.log(res)
                    this.areaList4 = res.data
                })
            },
            getGoodsCatParentList(id){
                const params = { //总的提交信息
                    cat_id : id
                }
                getGoodsCatParent(params).then(res => {
                    this.areaList7 = res.data || []
                })
            },
            goodsCatLists(){
                const params = { //总的提交信息

                }
                goodsCatList(params).then(res => {
                    this.areaList6 = res.data || []
                })
            },
            getRegionLists(){
                const params = { //总的提交信息

                }
                getRegion(params).then(res => {
                    this.areaList8 = res.data
                })
            },
            // 删除
            revoke({ row, column }) {
                let deIndex = 0
                this.tableData.forEach((element, index) => {
                    if (element.product_id === row.product_id) {
                        deIndex = index
                    }
                });
                this.tableData.splice(deIndex, 1)
                this.computedPrice()
            },
            onSubmit(status=0)
            {
                this.pageLoading = true
                this.$refs.basicForm.validate()
                this.$refs.userForm.validate()
                this.$refs.productForm.validate()
                if(this.tableData.length===0){
                    this.$message.error('产品信息不能为空，请添加产品！')
                    this.pageLoading = false
                    return
                }

                const mailReg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/
                if(this.userForm.userInfoI && !mailReg.test(this.userForm.userInfoI)){
                    this.$message.error('请输入正确的邮箱')
                    this.pageLoading = false
                    return
                }
                if(this.tendererForm.email && !mailReg.test(this.tendererForm.email)){
                    this.$message.error('请输入正确的邮箱')
                    this.pageLoading = false
                    return
                }
                if(this.tendeInvitForm.email && !mailReg.test(this.tendeInvitForm.email)){
                    this.$message.error('请输入正确的邮箱')
                    this.pageLoading = false
                    return
                }
                console.log(12345)
                console.log(this.basicForm.basicC)
                console.log(this.userForm.userInfoD)
                if(this.basicForm.basicC == 2){
                    if(this.userForm.userInfoF == '' || this.userForm.userInfoF == 0){
                        this.$message.error('请选择省份')
                        this.pageLoading = false
                        return
                    }
                    if(this.userForm.userInfoG == '' || this.userForm.userInfoG == 0){
                        this.$message.error('请选择城市')
                        this.pageLoading = false
                        return
                    }
                }
                const province = this.getProvinceName(this.userForm.userInfoF)
                const city = this.getCityName(this.userForm.userInfoG)

                if(this.basicForm.basicC !== 2){
                    this.basicForm.basicA_city = ''
                }
                let params = { //总的提交信息
                    entry_name: this.basicForm.basicC===2 ? `${this.basicForm.basicA_city}${this.basicForm.basicA_val}` : this.basicForm.basicA_val, //项目名称
                    entry_name_val: this.basicForm.basicA_val, //项目未拼接
                    entry_name_city: this.basicForm.basicA_city, //项目未拼接
                    project_source : this.basicForm.basicB, //项目来源
                    filing_type: this.basicForm.basicC, //项目覆盖范围
                    // is_cross_region : this.basicForm.basicD,//是否跨区
                    is_moem : this.basicForm.basicE,//MOEM设备配套
                    industry_id : this.basicForm.basicF,//客户行业
                    integrator : this.basicForm.basicG,//集成商
                    Integrator_province_id : this.basicForm.basicH,//是否跨区
                    winning_rate_id : this.basicForm.basicI,//赢单率
                    tender_date : this.basicForm.basicJ,//投标日期
                    bidding_form : this.basicForm.basicK,//招标形式
                    is_certificate_required : this.basicForm.basicL,//是否需要授权书
                    effective_date : this.basicForm.basicM,//有效期至
                    shipping_date : this.basicForm.basicN,//预计出货日期
                    country : this.userForm.userInfoE || '中国',
                    province : this.userForm.userInfoF,
                    city : this.userForm.userInfoG,
                    email : this.userForm.userInfoI,//邮箱
                    address : this.userForm.userInfoH,//地址
                    //最终用户信息
                    user_name : this.userForm.userInfoA,//最终用户名称
                    contact_name : this.userForm.userInfoB,//联系人姓名
                    contact_phone : this.userForm.userInfoC,//电话/手机
                    region : this.basicForm.basicC == '1' ? this.userForm.userInfoD.join(',') : 0,//区域
                    estimated_valuation : this.total || 0,
                    pid :pid,
                    //投标单位
                    unitarr : [
                        {
                            company_name :  this.tendererForm.companyName,
                            address : this.tendererForm.address,
                            contact_name : this.tendererForm.user,
                            telephone : this.tendererForm.phone,
                            mobile_phone : this.tendererForm.telephone,
                            unit_type : 1,
                            email : this.tendererForm.email
                        },
                        {
                            company_name :  this.tendeInvitForm.companyName,
                            address : this.tendeInvitForm.address,
                            contact_name : this.tendeInvitForm.user,
                            telephone : this.tendeInvitForm.phone,
                            unit_type : 2,
                            mobile_phone : this.tendererForm.telephone,
                            email : this.tendeInvitForm.email
                        },
                        {
                            company_name :  this.deliveryForm.companyName,
                            address : this.deliveryForm.address,
                            contact_name : this.deliveryForm.user,
                            unit_type : 3,
                            mobile_phone : this.tendererForm.telephone,
                            email : this.deliveryForm.email
                        },
                    ],
                    create_user_id : '1',
                    inventory : this.tableData,
                    project_description : this.basicForm.textarea,//项目说明情况
                    tracking_record : this.basicForm.textarea1,//项目跟踪记录
                }
                if(this.id){
                    params["reportingInformationId"] = this.id;
                    params["ydu"] = this.yd;
                    params["gx"] = 1;
                }
                if(this.mod_id){
                    params["reportingInformationId"] = this.mod_id
                }
                if(status===1){
                    params["status"] = 1
                }
                addBusinessOpportunity(params).then(res => {
                    if(res.code == 200){
                        this.$message.success({
                            message: "提交成功",
                            duration: 2000,
                            onClose(){
                                if (params["reportingInformationId"] || status!=1) {
                                    // window.location.href = '/index.php?ctl=Shante_Business_Reporting&met=opportunity'
                                    window.location.reload()
                                } else {
                                    window.location.reload()
                                }
                                this.pageLoading = false
                                // window.location.href = '/index.php?ctl=Shante_Business_Reporting&met=opportunity'
                            }
                        })
                        this.autoIno = 0;
                        if(this.useclear!=-1)
                        localStorage.setItem('s_garden_id',JSON.stringify({}))
                        if(this.id || this.mod_id){
                            // window.location.reload()
                            window.history.go(-1);
                        }
                    }else{
                        this.$message.success({message: res.msg, duration: 3000})
                        this.pageLoading = false
                    }
                })
            },
            closed() {
                if(this.id || this.mod_id){
                    // window.location.reload()
                    window.history.go(-1);
                }
            },
            userInfoASearch(query) {
                this.userForm.userInfoA = query
                this.userForm.userInfoB = ''
                this.userForm.userInfoC = ''
                this.userForm.userInfoD = ''
                this.userForm.userInfoE = ''
                this.userForm.userInfoF = ''
                this.userForm.userInfoG = ''
                this.userForm.userInfoH = ''
                this.userForm.userInfoI = ''
                this.userInfoALoading = true;
                let params = {
                    name: query
                }
                getZzuser(params).then(res=>{
                    this.userInfoALoading = false;
                    this.userInfoAOptions = res.data || [];
                })
                this.userInfoASearchValue = query
            },
            userInfoAChange() {
                this.userInfoAOptions.map(item=>{
                    if(item.endUser==this.userForm.userInfoA){
                        this.userForm.userInfoB = item.contact_name
                        this.userForm.userInfoC = item.telephone
                        this.userForm.userInfoE = item.country
                        this.userForm.userInfoF = item.province_id
                        this.userForm.userInfoG = item.city_id
                        this.userForm.userInfoH = item.address
                        this.userForm.userInfoI = item.email
                        this.userForm.userInfoD = item.region ? item.region.split(',') : []
                    }
                })
                if(this.userForm.userInfoF){
                    this.getCityListNew(this.userForm.userInfoF)
                }
            },
            getCityListNew(val) {
                let params = {
                    district_id: val
                }
                getDistrict(params).then(res=>{
                    this.cityList = res.data
                    this.basicForm.basicA_city = `${this.getProvinceName(this.userForm.userInfoF)}${this.getCityName(this.userForm.userInfoG)}`
                    this.$forceUpdate()
                })
            },
            productFSearch(query) {
                this.productNumber = 1
                this.productSearch = query
                this.getProductSearch()
            },
            handleProductChange(index){
                this.productNumber = index
                this.getProductSearch()
            },
            getProductSearch() {
                this.productFLoading = true;
                let line = ''
                let series = ''
                let cat_id = ''
                if(this.areaList6.length>0)
                {
                    this.areaList6.map(item=>{
                        if(item.cat_id===this.productForm.productA){
                            line = item.cat_name
                        }
                    })
                }
                if(this.areaList7.length>0){
                    this.areaList7.map(item=>{
                        if(item.cat_id===this.productForm.productB){
                            series = item.cat_name
                            cat_id = item.cat_id
                        }
                    })
                }
                // 搜索商品列表
                let params = {
                    common_name: this.productSearch,
                    product_pl8: line,
                    product_series: series,
                    cat_id: cat_id, // 根据最后一个cate_id从库里取值
                    pageNumber: this.productNumber,
                    pageSize: this.productSize
                }
                //标识符合
                params.mark = 1;
                productList(params).then(res=>{
                    this.productFLoading = false;

                    this.productFOptions = res.data.datas || [];
                    console.log("型号调试A",res.data.datas);
                    this.productTotal = res.data.count
                    this.$forceUpdate()
                })
            },
            productFChange() {
                this.productFOptions.map(item=>{
                    if(item.common_id==this.productForm.productF){
                        this.chanpindata = item
                        this.productLIAOHAOSearch(item.common_name)
                        this.productForm.productLIAOHAO = item.pino
                    }
                })
                this.productForm.productA = this.chanpindata.pl8_cart_id>0?this.chanpindata.pl8_cart_id:''
                if(!this.productForm.productA){
                    this.productForm.productB = ''
                    return
                }
                this.productForm.productB = this.chanpindata.cat_id>0?this.chanpindata.cat_id:''
            },
            //投标单位 公司名称搜索
            companyNameSearch(query) {
                this.tendererForm.companyName = query
                let params = {
                    type: 1,
                    company_name: query
                }
                getUnit(params).then(res=>{
                    this.areaList13 = res.data;
                })
            },
            // 根据料号搜索到产品线、产品系列、产品型号
            productLIAOHAOSearch(query)
            {
                this.productForm.productLIAOHAO = query
                let params = {
                    pino: query
                }
                productLIAOHAOSearch(params).then(res=>{
                    this.areaList15 = res.data
                })
            },
            handleproductLIAOHAO(item)
            {
                console.log(item)
                this.productFBWork = true
                this.productForm.productA = item.cat_parent_id
                this.getGoodsCatParentList(this.productForm.productA)
                if(Number(item.cat_id)<=0){
                  this.isShowProductB=true
                }else{
                    this.isShowProductB=false
                }
                this.productForm.productF = item.common_name
                this.productForm.productLIAOHAO = item.pino
                this.chanpindata = item
                this.$nextTick(()=>{
                    this.productForm.productB = item.cat_id
                })
            },
            handleCompany(item)
            {
                this.tendererForm =
                    {
                    companyName: item.company_name,
                    address: item.company_address,
                    user: item.cmpany_contact,
                    phone: item.company_mobile,
                    email: item.company_email,
                    telephone: item.company_phone,
                }
            },
            //招标单位 公司名称搜索
            companyNameSearch2(query) {
                this.tendeInvitForm.companyName = query
                let params = {
                    type: 2,
                    company_name: query
                }
                getUnit(params).then(res=>{
                    this.areaList9 = res.data;
                })
            },
            handleCompany2(item) {
                this.tendeInvitForm = {
                    companyName: item.company_name,
                    address: item.company_address,
                    user: item.cmpany_contact,
                    phone: item.company_mobile,
                    email: item.company_email,
                    telephone: item.company_phone,
                }
            },
            //出货单位
            getCh(){
                const params = { //总的提交信息

                }
                getCh(params).then(res => {
                    this.areaList5 = res.data
                    if(this.areaList5.length>0){
                        this.deliveryForm = {
                            companyName: this.areaList5[0].shop_name,
                            address: this.areaList5[0].shop_address,
                            shop_tel: this.areaList5[0].shop_tel,
                            user: this.areaList5[0].user_realname,
                            phone: this.areaList5[0].user_mobile,
                            email: this.areaList5[0].user_email,
                        }
                    }
                })
            },
            handleCh(item)
            {
                this.deliveryForm = {
                    companyName: item.shop_name,
                    address: item.shop_address,
                    user: item.user_realname,
                    phone: item.user_mobile,
                    email: item.user_email,
                }
            },
            //项目来源
            getProjectSource() {
                let params = {}
                getProjectSource(params).then(res=>{
                    this.areaList14 = []
                    if(res.data["find"]){
                        if(res.data["find"].title){
                            this.areaList14.push(res.data["find"])
                            this.basicForm.basicB = res.data["find"].id
                            return false
                        }
                    }
                    if(res.data.datas){
                        if(res.data.datas.length>0){
                            this.areaList14 = res.data.datas
                        }
                    }
                })
            },
            //获取url 参数
            getQueryString(name) {
                let query = window.location.search.substring(1);
                let vars = query.split("&");
                for (let i=0;i<vars.length;i++) {
                    let pair = vars[i].split("=");
                    if(pair[0] == name){
                        return pair[1];
                    }
                }
                return null;
            },
            // 根据id筛选省
            getProvinceName(val) {
                if(val) {
                    let province = this.provinceList.filter(item=>{
                        return item.district_id === this.userForm.userInfoF ? this.userForm.userInfoF : ''
                    })
                    if(province.length) {
                        return province[0].district_name
                    }else {
                        return ''
                    }
                } else {
                    return ''
                }
            },
            // 根据id筛选市
            getCityName(val) {
                if(val) {
                    let city = this.cityList.filter(item=>{
                        return item.district_id === this.userForm.userInfoG ? this.userForm.userInfoG : ''
                    })
                    if(city.length) {
                        return city[0].district_name
                    }else {
                        return ''
                    }
                }else {
                    return ''
                }
            },
            //获取详情
            getBusinessDetailAjax() {
                let params = {
                    id: this.id
                }
                if(this.mod_id){
                    params.id = this.mod_id
                }
                getBusinessDetailAjax(params).then(res => {
                    if(res.code===200){
                        const dataList = res.data
                        this.basicForm = {
                            basicA: dataList.entry_name,
                            basicA_val: dataList.entry_name_val,
                            basicA_city: dataList.entry_name_city,
                            basicB: dataList.project_source ? parseInt(dataList.project_source) : '',
                            basicC: dataList.filing_type,
                            // basicD: dataList.is_cross_region,
                            basicE: dataList.is_moem,
                            basicF: dataList.industry_id,
                            basicG: dataList.integrator,
                            basicH: dataList.Integrator_province_id,
                            basicI: dataList.winning_rate_id,
                            basicJ: dataList.tender_date,
                            basicK: dataList.bidding_form,
                            basicL: dataList.is_certificate_required,
                            basicM: dataList.effective_date,
                            basicN: dataList.shipping_date,
                            textarea: dataList.project_description,
                            textarea1: dataList.tracking_record
                        }
                        this.userForm = {
                            userInfoA: dataList.user_name,
                            userInfoB: dataList.contact_name,
                            userInfoC: dataList.contact_phone,
                            userInfoD: dataList.region.split(','),
                            userInfoE: dataList.country,
                            userInfoF: dataList.province != '' ? dataList.province : '',
                            userInfoG: dataList.city  != '' ? dataList.city : '',
                            userInfoH: dataList.address,
                            userInfoI: dataList.email,
                        }
                        //获取地市列表
                        if(this.userForm.userInfoF) this.getCityList(this.userForm.userInfoF)
                        if(dataList.unit.length>0){
                            this.tendererForm = {
                                companyName: dataList.unit[0].company_name,
                                address: dataList.unit[0].address,
                                user: dataList.unit[0].contact_name,
                                phone: dataList.unit[0].mobile_phone,
                                email: dataList.unit[0].email,
                                telephone: dataList.unit[0].telephone,
                            }
                        }
                        if(dataList.unit.length>1){
                            this.tendeInvitForm = {
                                companyName: dataList.unit[1].company_name,
                                address: dataList.unit[1].address,
                                user: dataList.unit[1].contact_name,
                                phone: dataList.unit[1].mobile_phone,
                                email: dataList.unit[1].email,
                                telephone: dataList.unit[1].telephone,
                            }
                        }
                        if(dataList.unit.length>2){
                            this.deliveryForm = {
                                companyName: dataList.unit[2].company_name,
                                address: dataList.unit[2].address,
                                user: dataList.unit[2].contact_name,
                                phone: dataList.unit[2].mobile_phone,
                                email: dataList.unit[2].email,
                                telephone: dataList.unit[2].telephone,
                            }
                        }
                        this.tableData = []
                        dataList.product.map(item=>{
                            this.tableData.push({
                                productA : item.common_name,
                                product_id : item.product_id,
                                product_line_id : item.product_line_id,
                                product_series_id : item.product_series_id,
                                id: item.product_id,
                                estimated_quantity : item.estimated_quantity,
                                productB: item.pino,
                                productC: item.common_price,
                            })
                        })
                        this.total = Number(dataList.estimated_valuation)
                    }else{
                        this.$message.error(res.msg)
                    }
                })
            },
            getProvinceList() {
                let params = {
                    district_id: 0
                }
                getDistrict(params).then(res=>{
                    this.provinceList = res.data
                })
            },
            getCityList(val) {
                let params = {
                    district_id: val
                }
                getDistrict(params).then(res=>{
                    this.cityList = res.data
                })
            },
            changeProvince(){
                this.userForm.userInfoG = ''
                this.cityList = []
                this.basicForm.basicA_city = `${this.getProvinceName(this.userForm.userInfoF)}${this.getCityName(this.userForm.userInfoG)}`
                this.getCityList(this.userForm.userInfoF)
            },
            changeCity(){
                this.basicForm.basicA_city = `${this.getProvinceName(this.userForm.userInfoF)}${this.getCityName(this.userForm.userInfoG)}`
                if(this.id || this.mod_id) return
                let province = this.provinceList.filter(item=>{
                    return item.district_id===this.userForm.userInfoF
                })
                let city = this.cityList.filter(item=>{
                    return item.district_id===this.userForm.userInfoG
                })
                // if(province.length>0 && city.length>0){
                //     this.basicForm.basicA = province[0].district_name+city[0].district_name+this.basicForm.basicA
                // }
            },
            getUserInformation() {
                getUserInformation({}).then(res=>{
                    if(res.data){
                        // this.userForm.userInfoA = res.data.companyname
                        // this.userForm.userInfoB = res.data.c_realname
                        // this.userForm.userInfoC = res.data.c_mobile
                        // this.userForm.userInfoF = res.data.province_id
                        // this.userForm.userInfoG = res.data.city_id
                        // this.userForm.userInfoH = res.data.shop_address
                        // this.userForm.userInfoI = res.data.c_email
                        this.basicForm.basicF = res.data.industry_id
                        // if(this.userForm.userInfoF) this.getCityList(this.userForm.userInfoF)
                    }
                })
            },
            //判断项目来源 >0 来源默认2
            // isHangye() {
            //     isHangye({}).then(res=>{
            //         if(res>0){
            //             this.basicForm.basicB = 2
            //         }
            //     })
            // },
            // isQudao() {
            //     isQudao({}).then(res=>{
            //         if(res>0){
            //             this.basicForm.basicB = 1
            //         }
            //     })
            // }
        },
        watch:{
            'basicForm.basicM'(o,l){
                console.log(o);
                var now_time = moment(Date()).startOf('day').format('x');
                var select_tome = moment(o).startOf('day').format('x');
                if (select_tome <= now_time){
                    this.basicForm.basicM = l;
                    this.$message.error("请选择今天以后的时间!");
                }
            },
            'productForm.productA'(o,l){
                if (!this.productFBWork) {
                    this.productForm.productB = ""
                    this.productForm.productE = ''
                    this.productForm.productF= ""
                    this.productForm.productLIAOHAO = ""
                    this.productFBWork = false
                }
                this.getGoodsCatParentList(this.productForm.productA)
                setTimeout(() => {
                   // this.productFSearch()

                }, 300);
            },
            'productForm.productB'() {
                if (!this.productFBWork) {
                    this.productForm.productE = ''
                    this.productForm.productF= ""
                    this.productForm.productLIAOHAO = ""
                    this.productFBWork = false
                }
                this.productFSearch()
            },
            'productForm.productLIAOHAO'() {
                this.productForm.productE = ''
            },
            'basicForm.basicC'(val){

                if(val===2){
                    this.basicFormRule.basicN[0].required = true
                    this.userFormRule.userInfoD[0].required = false
                    // this.userFormRule.userInfoE[0].required = true
                    this.userFormRule.userInfoF[0].required = true
                    this.userFormRule.userInfoG[0].required = true
                    // this.userFormRule.userInfoH[0].required = true
                }else{
                    this.basicFormRule.basicN[0].required = false
                    this.userFormRule.userInfoD[0].required = true
                    // this.userFormRule.userInfoE[0].required = false
                    this.userFormRule.userInfoF[0].required = false
                    this.userFormRule.userInfoG[0].required = false
                    // this.userFormRule.userInfoH[0].required = false
                }
            }
        }
    })
</script>
</body>

</html>
