<?php
namespace app\api\controller\flbooth;

use app\common\controller\Api;
use addons\flbooth\library\AliyunSdk\Video as Vod;

/**
 * flbooth验证接口
 */
class Video extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];
    
    public function _initialize()
    {
        parent::_initialize();
		$this->model = new \app\api\model\flbooth\Video;
    }

    /**
     * 获取视频上传地址和凭证
     *
     */
    public function getUploadProof($name)
    {
		$config = get_addon_config('flbooth');
        $vod = new Vod($config['video']['regionId'], $config['video']['accessKeyId'], $config['video']['accessKeySecret']);
        $sts = $vod->createUploadVideo($name, $name, $config['video']['workflowId']);
		if(!$sts){
			$this->error(__('获取上传凭证失败！'));
		}
		$uploadAuth = json_decode(base64_decode($sts->UploadAuth));
		$uploadAddress = json_decode(base64_decode($sts->UploadAddress));
		$ossUrl = parse_url($uploadAddress->Endpoint)['scheme'] . '://' . $uploadAddress->Bucket .'.'.parse_url($uploadAddress->Endpoint)['host'];
		$policy = base64_encode('{"expiration":"'.$uploadAuth->ExpireUTCTime.'","conditions":[["content-length-range",0,1048576000]]}');
		$signature = base64_encode(hash_hmac('sha1', $policy, $uploadAuth->AccessKeySecret, true));
		$this->success('ok', [
			'ossUrl' => $ossUrl,
			'file' => 'file',
			'videoId' => $sts->VideoId,
			'formData' => [
				'OSSAccessKeyId' => $uploadAuth->AccessKeyId,
				'policy' => $policy,
				'key' => $uploadAddress->FileName,
				'x-oss-security-token' => $uploadAuth->SecurityToken,
				'success_action_status' => '200',
				'Signature' => $signature
			]
		]);
    }

}
