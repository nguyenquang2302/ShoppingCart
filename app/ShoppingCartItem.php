<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShoppingCartItem extends Model
{
    /**
     * The table associated with the model
     * You can change this as per your wishes. MAke sure to bring the same change in the migration.
     * 
     * @var string
     */
    protected $table = 'shopping_cart';

    /**
     * The connection name for the model.
     * By default, all Eloquent models use the default database connection 
     * configured in config/database.php. 
     * But you may choose to use a different database altogether. 
     *
     * @var string
     */
    /*
     * This has been commented out.
     * protected $connection = 'connection-name';
     */
}
