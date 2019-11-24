<?php

namespace Lsshu\LaravelCodeLogin\models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class AdminUser extends \Encore\Admin\Auth\Database\Administrator
{
    use Notifiable;
    protected $fillable = ['username', 'name'];

    /**
     * 微信信息
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function wechats()
    {
        $path = config('admin.route.prefix');
        return $this->belongsToMany(WechatUserInfo::class, 'wechat_to_users',  'user_id','wechat_id')->where('path',$path)->withPivot('path');
    }
}
