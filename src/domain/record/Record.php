<?php declare(strict_types=1);

namespace app\enitites\record;

/**
 * Record class.
 */
class Record 
{
    /** @var int */
    public $id;

    /** @var int */
    public $client_id;
    
    /** @var int */
    public $hall_id;
    
    /** @var int */
    public $payment_id;
    
    /** @var int */
    public $promo_id;
    
    /** @var float */
    public $total;

    /** @var string */
    public $comment;
    
    /** @var int */
    public $status;

    /** @var int */
    public $created_at;

    /** @var int */
    public $updated_at;

    /** @var int */
    public $created_by;

    /** @var int */
    public $updated_by;


}