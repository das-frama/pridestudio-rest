<?php
declare(strict_types=1);

namespace App\Entities;

use App\Entities\Base\AbstractEntity;
use MongoDB\BSON\ObjectId;

/**
 * Record AbstractEntity class.
 */
class Record extends AbstractEntity
{
    // Statuses.
    const STATUS_CANCELED = 0;
    const STATUS_NEW = 1;
    const STATUS_PREPAID = 2;
    const STATUS_NOT_PAID = 3;
    const STATUS_PAID = 4;
    const STATUS_CASH = 5;
    const STATUS_DONE = 10;

    public string $id;
    public string $client_id;
    public string $hall_id;
    /** @var Reservation[] */
    public array $reservations;
    public array $service_ids;
    public ?Payment $payment;
    public string $coupon_id;
    public int $total;
    public string $comment;
    public string $client_comment;
    public int $status;
    public int $created_at;
    public int $updated_at;
    public string $created_by;
    public string $updated_by;

    // Relations.
    public ?Client $client;
    public ?Hall $hall;
    public ?Coupon $coupon;
    /** @var Service[] */
    public ?array $services;

    protected array $public = [
        'id',
        'client_id',
        'hall_id',
        'reservations',
        'service_ids',
        'payment',
        'coupon_id',
        'total',
        'status',
        'created_at',
        'comment',
        'client_comment',
        'client',
        'hall',
        'services',
    ];
    protected array $fillable = [
        'hall_id',
        'reservations',
        'payment',
        'coupon_id',
        'total',
        'status',
        'comment',
        'client_comment',
        'client'
    ];

    /**
     * {@inheritDoc}
     */
    public function load(array $data, array $safe = []): void
    {
        parent::load($data, $safe);
        if ($safe === []) {
            $safe = $this->fillable;
        }
        if (isset($data['reservations']) && is_array($data['reservations']) && in_array('reservations', $safe)) {
            $this->reservations = [];
            foreach ($data['reservations'] as $d) {
                $reservation = new Reservation();
                $reservation->load($d);
                $this->reservations[] = $reservation;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function bsonSerialize(): array
    {
        $bson = parent::bsonSerialize();
        if (isset($this->client_id)) {
            $bson['client_id'] = new ObjectId($this->client_id);
        }
        if (isset($this->hall_id)) {
            $bson['hall_id'] = new ObjectId($this->hall_id);
        }
        if (isset($this->coupon_id)) {
            $bson['coupon_id'] = new ObjectId($this->coupon_id);
        }
        if (isset($this->service_ids)) {
            $bson['service_ids'] = array_map(function (string $id) {
                return new ObjectId($id);
            }, $this->service_ids);
        }

        return $bson;
    }
}
