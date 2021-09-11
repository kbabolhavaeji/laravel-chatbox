<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

/**
 * generate a code for public rooms using their id's
 * @param $id
 * @return string
 */
function createPublicRoomCode($id): string
{
    return simple_two_way_crypt($id);
}

/**
 * this is a oneway method to create a unique name for using private room between two users
 * it always returns the same name which created by user's id
 * @param $id
 * @return string
 */
function createPrivateRoomCode($id){
    $hashed_key = str_split(substr(md5(Auth::user()->id), 0, 6).substr(md5($id), 0, 6));
    $hashed_name = implode(Arr::sort($hashed_key));

    return simple_two_way_crypt($hashed_name);
}

/**
 * this encrypts and decrypts the sorted name of private rooms and returns it
 * @param $string
 * @param string $action
 * @return false|string
 */
function simple_two_way_crypt($string, $action = 'e' ) {

    $secret_key = 'abcdefghijklmnop123456';
    $secret_iv = 'QRSTUVWXYZ789';

    $output = false;
    $encrypt_method = "AES-256-CBC";
    $key = hash( 'sha256', $secret_key );
    $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

    if( $action == 'e' ) {
        $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
    }
    else if( $action == 'd' ){
        $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
    }

    return $output;
}
