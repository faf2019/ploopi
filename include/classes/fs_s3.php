<?php

namespace ploopi;

use Aws\S3\S3Client;

/**
 * Gestion d'accès à l'espace de stockage sur S3/Minio.
 * Principalement axé sur le téléchargement de fichiers
 * Le reste n'est pas utilisé pour le moment (copie de dossiers : gestion entre S3 et local plus compliqué)
 *
 * @package ploopi
 * @subpackage filesystem
 * @copyright Auzou
 * @license GNU General Public License (GPL)
 * @author Louis Rabiet
 */
abstract class fs_s3
{

    /**
     * Test l'existence du fichier sur le s3
     */
    public static function file_exists(string $filepath): bool
    {
        $s3 = new S3Client([
            'version' => 'latest',
            'region' => _PLOOPI_S3_REGION,
            'bucket_endpoint' => false,
            'use_path_style_endpoint' => true,
            'endpoint' => _PLOOPI_S3_ENDPOINT_URL,
            'validate' => false,
            'http' => [
                'verify' => false, // Disable SSL certificate verification
            ],
            'credentials' =>
                [
                    'key' => _PLOOPI_S3_AUTH_KEY,
                    'secret' => _PLOOPI_S3_AUTH_SECRET,
                ]
        ]);

        // Register the stream wrapper from an S3Client object
        // important ca permet d'utiliser les fonctions classiques de gestion de fichier avec l'url s3://bucket/key
        $s3->registerStreamWrapper();

        return file_exists($filepath);
    }

    /**
     * Supprime un fichier d'un bucket
     */
    public static function delete(string $bucket, string $key): bool
    {
        $s3 = new S3Client([
            'version' => 'latest',
            'region' => _PLOOPI_S3_REGION,
            'bucket_endpoint' => false,
            'use_path_style_endpoint' => true,
            'endpoint' => _PLOOPI_S3_ENDPOINT_URL,
            'validate' => false,
            'http' => [
                'verify' => false, // Disable SSL certificate verification
            ],
            'credentials' =>
                [
                    'key' => _PLOOPI_S3_AUTH_KEY,
                    'secret' => _PLOOPI_S3_AUTH_SECRET,
                ]
        ]);

        // Register the stream wrapper from an S3Client object
        // important ca permet d'utiliser les fonctions classiques de gestion de fichier avec l'url s3://bucket/key
        $s3->registerStreamWrapper();

        if(!$s3-> deleteObject([
            'Bucket' => $bucket,
            'Key' => "$key",
        ])){
            return false;
        };
        return true;
    }

    /**
     * Copie un fichier d'un bucket dans le meme bucket avec un nom different
     */
    public static function copy(string $bucket, string $key, string $destfilename): bool
    {
        $s3 = new S3Client([
            'version' => 'latest',
            'region' => _PLOOPI_S3_REGION,
            'bucket_endpoint' => false,
            'use_path_style_endpoint' => true,
            'endpoint' => _PLOOPI_S3_ENDPOINT_URL,
            'validate' => false,
            'http' => [
                'verify' => false, // Disable SSL certificate verification
            ],
            'credentials' =>
                [
                    'key' => _PLOOPI_S3_AUTH_KEY,
                    'secret' => _PLOOPI_S3_AUTH_SECRET,
                ]
        ]);

        // Register the stream wrapper from an S3Client object
        // important ca permet d'utiliser les fonctions classiques de gestion de fichier avec l'url s3://bucket/key
        $s3->registerStreamWrapper();

        if(!$s3-> copyObject([
                'Bucket' => $bucket,
                'CopySource' => "$bucket/$key",
                'Key' => "$destfilename",
            ])){
            return false;
        };
        self::delete($bucket, $key);
        return true;
    }
    /**
     * Téléchargement d'un fichier vers le systeme de fichier local.
     *
     * @param string $bucket bucket du fichier
     * @param string $key key/nom du fichier
     * @param string $destfilename nom du fichier tel qu'il apparaîtra au moment du téléchargement
     * @return boolean false si le fichier n'existe pas, rien sinon
     */

    public static function downloadfilelocally(string $bucket, string $key, string $destfilename): bool
    {
        $s3 = new S3Client([
            'version' => 'latest',
            'region' => _PLOOPI_S3_REGION,
            'bucket_endpoint' => false,
            'use_path_style_endpoint' => true,
            'endpoint' => _PLOOPI_S3_ENDPOINT_URL,
            'validate' => false,
            'http' => [
                'verify' => false, // Disable SSL certificate verification
            ],
            'credentials' =>
                [
                    'key' => _PLOOPI_S3_AUTH_KEY,
                    'secret' => _PLOOPI_S3_AUTH_SECRET,
                ]
        ]);

        // Register the stream wrapper from an S3Client object
        // important ca permet d'utiliser les fonctions classiques de gestion de fichier avec l'url s3://bucket/key
        $s3->registerStreamWrapper();

        $filepath = "s3://$bucket/$key";

        if (file_exists($filepath)) {
            $file = $s3->getObject(['Bucket' => $bucket, 'Key' => $key]);
            $body = $file->get('Body');
            $body->rewind();
            if (!file_put_contents($destfilename, $body)) {
                return false;
            };
            return true;
        } else return false;
    }

    /**
     * Upload d'un fichier vers le s3.
     *
     * @param string $bucket bucket du fichier
     * @param string $key key/nom du fichier
     * @param string $destfilename nom du fichier tel qu'il apparaîtra au moment du téléchargement
     * @return boolean false si le fichier n'existe pas, rien sinon
     */

    public static function upload(string $bucket, string $key, string $originFile): bool
    {
        $s3 = new S3Client([
            'version' => 'latest',
            'region' => _PLOOPI_S3_REGION,
            'bucket_endpoint' => false,
            'use_path_style_endpoint' => true,
            'endpoint' => _PLOOPI_S3_ENDPOINT_URL,
            'validate' => false,
            'http' => [
                'verify' => false, // Disable SSL certificate verification
            ],
            'credentials' =>
                [
                    'key' => _PLOOPI_S3_AUTH_KEY,
                    'secret' => _PLOOPI_S3_AUTH_SECRET,
                ]
        ]);

        // Register the stream wrapper from an S3Client object
        // important ca permet d'utiliser les fonctions classiques de gestion de fichier avec l'url s3://bucket/key
        $s3->registerStreamWrapper();

        if (file_exists($originFile)) {
            $file = $s3->getObject(['Bucket' => $bucket, 'Key' => $key, 'Body' => fopen($originFile, 'r')]);
            $result = $s3->PutObjectTagging(['Bucket' => $bucket,
                'Key' =>  $key,
                'Tagging' => [
                    'TagSet' => [
                        [
                            'Key' => 'isbn',
                            'Value' => '1'
                        ],
                        [
                            'Key' => 'id_article',
                            'Value' => '1'
                        ]

                    ]
                ]
            ]);
            return true;
        } else return false;
    }

    /**
     * Téléchargement d'un fichier vers le navigateur. Complète automatiquement les entêtes en renseignant notamment le type mime.
     *
     * @param string $bucket bucket du fichier
     * @param string $key key/nom du fichier
     * @param string $destfilename nom du fichier tel qu'il apparaîtra au moment du téléchargement
     * @param boolean $deletefile true si le fichier doit être supprimé après téléchargement
     * @param boolean $attachment true si le fichier doit être envoyé en "attachment", false si il doit être envoyé "inline"
     * @param boolean $die true si la fonction doit arrêter le script
     *
     * @return boolean false si le fichier n'existe pas, rien sinon
     */

    public static function downloadfile(string $bucket, string $key, string $destfilename, bool $deletefile = false, bool $attachment = true, bool $die = true): bool
    {
        clearstatcache();

        $s3 = new S3Client([
            'version' => 'latest',
            'region' => _PLOOPI_S3_REGION,
            'bucket_endpoint' => false,
            'use_path_style_endpoint' => true,
            'endpoint' => _PLOOPI_S3_ENDPOINT_URL,
            'validate' => false,
            'http' => [
                'verify' => false, // Disable SSL certificate verification
            ],
            'credentials' =>
                [
                    'key' => _PLOOPI_S3_AUTH_KEY,
                    'secret' => _PLOOPI_S3_AUTH_SECRET,
                ]
        ]);

        // Register the stream wrapper from an S3Client object
        // important ca permet d'utiliser les fonctions classiques de gestion de fichier avec l'url s3://bucket/key
        $s3->registerStreamWrapper();
        $filepath = "s3://$bucket/$key";


        if (file_exists($filepath)) {
            buffer::clean(true);

            @set_time_limit(0);

            $size = $s3->getObjectAttributes(['Bucket' => $bucket, 'Key' => $key,
                'ObjectAttributes' => [
                    'ObjectSize'
                ]])->get('ObjectSize');

            $chunksize = 1 * (1024 * 1024);

            header('Content-Type: ' . ($ct = fs::getmimetype($destfilename)));
            header('Content-Length: ' . $size);

            if (fs::file_getextension($destfilename) == 'svgz') header('Content-Encoding: gzip');
            else header('Content-Encoding: identity');

            if ($attachment) header("Content-disposition: attachment; filename=\"{$destfilename}\"");
            else header("Content-disposition: inline; filename=\"{$destfilename}\"");

            header('Accept-Ranges: bytes');
            header('Pragma: private');

            if (current(explode('/', $ct)) == 'image') {
                header("Expires: " . date(DATE_RFC822, time() + 315360000));
                header('Cache-Control: private, max-age=315360000, pre-check=315360000');
            } else {
                header('Expires: Sat, 1 Jan 2000 05:00:00 GMT');
                header('Cache-control: private');
            }

            ob_start();
            if ($fp = fopen($filepath, 'r')) {
                while (!feof($fp) && connection_status() == 0) {
                    echo fread($fp, $chunksize);
                    ob_flush();
                }
                fclose($fp);
                ob_end_flush();
            } else {
                header('Content-type: text/html; charset=iso-8859-1');
                system::kill('Impossible d\'ouvrir le fichier');
            }

            if ($deletefile && is_writable($filepath)) @unlink($filepath);

            if ($die) system::kill(null, true);

            return true;
        } else return false;
    }

}