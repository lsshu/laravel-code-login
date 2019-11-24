<?php
/**
 * Created by PhpStorm.
 * User: lsshu
 * Date: 2019/11/23
 * Time: 17:29
 */

namespace Lsshu\LaravelCodeLogin\models;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
class WechatUserInfo extends BaseModel
{
    use SoftDeletes;
    protected $fillable = ['openid','nickname','sex','language','city','province','country','headimgurl'];
    const SEX = '0';
    const SEX_MALE = '1';
    const SEX_WOMAN = '2';
    public static $sexMap=[
        self::SEX =>'未知',
        self::SEX_MALE =>'男',
        self::SEX_WOMAN =>'女',
    ];

    /**
     * A wechat belongs to many users.
     * @return BelongsToMany
     */
    public function users()
    {
        $relatedModel = config('admin.database.users_model');
        $path = config('admin.route.prefix');
        return $this->belongsToMany($relatedModel, 'wechat_to_users', 'wechat_id', 'user_id')->where('path',$path)->withPivot('path');
    }
}