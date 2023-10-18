<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
error_log("banner.php start");

require_once 'vendor/autoload.php';
error_log("banner.php 2");
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
error_log("banner.php 3");
use RdKafka\Producer;
error_log("banner.php 4");
class BannerProducer {

    private $producer;
    private $topicName;

    public function __construct($brokers, $topicName) {
        $config = new RdKafka\Conf();

        // Устанавливаем брокеры через конфигурацию
        $config->set('metadata.broker.list', $brokers);
        $config->set('log_level', (string)LOG_DEBUG);
        $config->set('debug', 'all');
        error_log("Setting Kafka brokers to: " . $brokers);

        $this->producer = new Producer($config);

        // Добавим вывод наших настроек для проверки
        error_log("Kafka config bootstrap.servers: " . $config->dump()['bootstrap.servers']);
        $this->producer->addBrokers($brokers);  // Этот метод принимает строку с брокерами и устанавливает их
        $this->topicName = $topicName;
        error_log("banner.php 6");
    }

    public function sendBannerEvent() {
        $data = [
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'view_date' => date('Y-m-d H:i:s'),
            'page_url' => $_SERVER['HTTP_REFERER'],
            // 'views_count' will be updated by the consumer side after reading from the Kafka topic
            'views_count' => 1
        ];

        $topic = $this->producer->newTopic($this->topicName);
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($data));
        $this->producer->poll(0);
        error_log("banner.php 11");
    }
}
error_log("banner.php continue");

$brokersValue = 'kafka:9092';
error_log("Using Kafka brokers: $brokersValue");
$producer = new BannerProducer($brokersValue, 'banner_topic');
$producer->sendBannerEvent();

// Отправка изображения в ответе
header('Content-Type: image/png');
readfile('mickey-mouse-logo-1.png');
