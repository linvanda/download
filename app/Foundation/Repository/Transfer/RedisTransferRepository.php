<?php

namespace App\Foundation\Repository\Transfer;

use App\Domain\Transfer\DownloadTicket;
use App\Domain\Transfer\ITransferRepository;
use ReflectionClass;
use WecarSwoole\RedisFactory;
use WecarSwoole\Repository\Repository;

class RedisTransferRepository extends Repository implements ITransferRepository
{
    private $redis;

    public function __construct()
    {
        $this->redis = RedisFactory::build('main');
    }

    public function addDownloadTicket(DownloadTicket $ticket)
    {
        $info = [
            'id' => $ticket->ticket(),
            'task_id' => $ticket->taskId(),
            'create_time' => $ticket->createTime,
        ];

        $this->redis->set($this->getTicketRedisKey($ticket->ticket()), json_encode($info), 600);
    }

    public function getDownloadTicket(string $ticketId): ?DownloadTicket
    {
        if (!$info = $this->redis->get($this->getTicketRedisKey($ticketId))) {
            return null;
        }
        
        $info = json_decode($info, true);

        $ticket = (new ReflectionClass(DownloadTicket::class))->newInstanceWithoutConstructor();
        $ticket->id = $info['id'];
        $ticket->taskId = $info['task_id'];
        $ticket->createTime = $info['create_time'];

        return $ticket;
    }

    private function getTicketRedisKey(string $ticketId): string
    {
        return 'download_ticket_' . md5($ticketId);
    }
}
