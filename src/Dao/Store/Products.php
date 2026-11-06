<?php

namespace Dao\Store;

use Dao\Table;

const STORE_PRODUCTS_SELECT_ALL = "SELECT `productId`,`productName`,`productDescription`,`productPrice`,`productImgUrl`,`productStatus` FROM products;";
const STORE_PRODUCTS_INSERT = "insert into products (`productName`,`productDescription`,`productPrice`,`productImgUrl`,`productStatus`)
values
  ( 
    :productName, 
    :productDescription, 
    :productPrice, 
    :productImgUrl, 
    :productStatus
  );";
const STORE_PRODUCTS_UPDATE = "update products set 
  productName = productName,
  productDescription = productDescription,
  productPrice = productPrice,
  productImgUrl = productImgUrl,
  productStatus = productStatus
where 
  productId = 'productId';";
const STORE_PRODUCTS_DELETE = "delete from products where productId = 'productId';";


class Products extends Table{
    private function __construct()
    {
        throw new \Exception('Not implemented');
    }
    private function __clone()
    {
        throw new \Exception('Not implemented');
    }

    /*
    CREATE TABLE
    `products` (
        `productId` int(11) NOT NULL AUTO_INCREMENT,
        `productName` varchar(255) NOT NULL,
        `productDescription` text NOT NULL,
        `productPrice` decimal(10, 2) NOT NULL,
        `productImgUrl` varchar(255) NOT NULL,
        `productStock` int(11) NOT NULL DEFAULT 0,
        `productStatus` char(3) NOT NULL,
        PRIMARY KEY (`productId`)
    ) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8mb4;
    */

    public static function getAll() : array {
        return self::obtenerRegistros(
            STORE_PRODUCTS_SELECT_ALL
        );
    }
    public static function getById(int $productId) : array 
    {
        return self::obtenerUnRegistro(
            STORE
            )    
    }
}