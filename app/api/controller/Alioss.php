<?php

namespace app\api\controller;

use app\common\model\Attachment;
use app\common\controller\Frontend;

class Alioss extends Frontend
{
    protected array $noNeedPermission = ['callback'];

    /**
     * 细目
     * @var string
     */
    protected string $topic = 'default';

    public function initialize(): void
    {
        parent::initialize();
    }

    public function callback(): void
    {
        $data       = $this->request->post();
        $params     = [
            'topic'    => $this->topic,
            'admin_id' => 0,
            'user_id'  => $this->auth->id,
            'url'      => $data['url'],
            'width'    => $data['width'] ?? 0,
            'height'   => $data['height'] ?? 0,
            'name'     => mb_substr(htmlspecialchars(strip_tags($data['name'])), 0, 100),
            'size'     => $data['size'],
            'mimetype' => $data['type'],
            'storage'  => 'alioss',
            'sha1'     => $data['sha1']
        ];
        $attachment = new Attachment();
        $attachment->data(array_filter($params));
        $attachment->save();
    }
}