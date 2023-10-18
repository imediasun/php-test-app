<?php
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use RdKafka\Consumer;
use RdKafka\ConsumerTopic;

class BannerConsumer {
    private $consumer;
    private $pdo;

    public function __construct() {
        $config = new RdKafka\Conf();
        $this->consumer = new Consumer($config);
        $this->consumer->addBrokers('kafka:9092');

        // Создайте подключение к базе данных
        $this->pdo = new PDO(getenv('DB_DSN'), getenv('MYSQL_USER'), getenv('MYSQL_PASSWORD'));
    }

    public function consume($topicName) {
        $topic = $this->consumer->newTopic($topicName);

        // Здесь -1 означает, что консьюмер будет получать сообщения с последнего коммита
        $topic->consumeStart(0, RD_KAFKA_OFFSET_END);

        while (true) {
            $message = $topic->consume(0, 1000);
            if ($message->err) {
                echo "Error: {$message->errstr()}\n";
            } else {
                $this->processMessage($message->payload);
            }
        }
    }

    private function processMessage($messagePayload) {
        $data = json_decode($messagePayload, true);

        // Предположим, что ваша таблица называется 'banner_views'
        $sql = "INSERT INTO page_views (ip_address, user_agent, view_date, page_url, views_count) 
            VALUES (?, ?, ?, ?, 1)
            ON DUPLICATE KEY UPDATE views_count = views_count + 1, view_date = VALUES(view_date)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$data['ip_address'], $data['user_agent'], $data['view_date'], $data['page_url']]);
    }
}

// Пример использования:
$consumer = new BannerConsumer();
$consumer->consume('banner_topic');
