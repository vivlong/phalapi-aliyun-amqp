# 阿里云AMQP扩展
PhalApi 2.x扩展类库，基于Aliyun的AMQP扩展。

## 安装和配置
修改项目下的composer.json文件，并添加：  
```
    "vivlong/phalapi-aliyun-amqp":"dev-master"
```
然后执行```composer update```。  

安装成功后，添加以下配置到/path/to/phalapi/config/app.php文件：  
```php
    /**
     * 阿里云AMQP相关配置
     */
    'AliyunAmqp' =>  array(
        'accessKeyId'       => '<yourAccessKeyId>',
        'accessKeySecret'   => '<yourAccessKeySecret>',
        'port'              => '5672',
        'endpoint'          => '*',
        'virtualHost'       => 'test',
        'resourceOwnerId'   => '<yourAccountId>',
    ),
```
并根据自己的情况修改填充。  

## 注册
在/path/to/phalapi/config/di.php文件中，注册：  
```php
$di->amqp = function() {
        return new \PhalApi\AliyunAmqp\Lite();
};
```

## 使用
```php
  \PhalApi\DI()->amqp->send($content);
```  
