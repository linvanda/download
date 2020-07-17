<?php

namespace App\Domain\Transfer;

interface ITransferRepository
{
    public function addDownloadTicket(DownloadTicket $ticket);

    public function getDownloadTicket(string $ticketId): ?DownloadTicket;
}
