<?php
namespace PhalApi\AliyunAmqp;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Lite {

    protected $config;

    protected $client;

    public function __construct($config = NULL) {
        $this->config = $config;
        if ($this->config === NULL) {
            $this->config = \PhalApi\DI()->config->get('app.AliyunAmqp');
        }
        $accessKeyId        = $this->config['accessKeyId'];
        $accessKeySecret    = $this->config['accessKeySecret'];
        $host               = $this->config['endpoint'];
        $port               = $this->config['port'];
        $virtualHost        = $this->config['virtualHost'];
        $resourceOwnerId    = $this->config['resourceOwnerId'];
        try {
            $username = $this->getUser($resourceOwnerId, $accessKeyId);
            $password = $this->getPassword($accessKeySecret);
            $connection = new AMQPStreamConnection($host, $port, $username, $password, $virtualHost, false);
            $this->client = $connection;
        } catch (OssException $e) {
            \PhalApi\DI()->logger->error($e->getMessage());
        }
    }

    private function getUser($resourceOwnerId, $accessKey) {
        $t = '0:' . $resourceOwnerId . ':' . $accessKey;
        return base64_encode($t);
    }

    private function getPassword($accessSecret) {
        $ts = (int)(microtime(true)*1000);
        $value = utf8_encode($accessSecret);
        $key = utf8_encode((string)$ts);
        $sig = strtoupper(hash_hmac('sha1', $value, $key, FALSE));
        return base64_encode(utf8_encode($sig . ':' . $ts));
    }

    public function getClient() {
        return $this->client;
    }

    public function getConfig() {
        return $this->config;
    }

    public function send($queueName, $content)
    {
        $channel = $this->client->channel();
        $channel->queue_declare($queueName, false, true, false, false);
        $msg = new AMQPMessage($content, array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
        $channel->basic_publish($msg, '', $queueName);
        $channel->close();
    }

    public function receive($queueName, $content)
    {
        $channel = $this->client->channel();
        $channel->queue_declare($queueName, false, true, false, false);
        echo " [*] Waiting for messages. To exit press CTRL+C\n";
        $callback = function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };
        $channel->basic_consume($queueName, '', false, true, false, false, $callback);
        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
    }

}
