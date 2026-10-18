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
     * Client S3 partagé par la requête
     *
     * @var S3Client
     */

    private static $objClient = null;

    /**
     * Renvoie le client S3, créé une fois par requête, avec le gestionnaire de flux
     * « s3:// » enregistré : les fonctions de fichiers de PHP (file_exists, fopen,
     * unlink...) s'appliquent alors aux objets du dépôt.
     *
     * Le certificat du serveur est vérifié. Il ne l'était jamais ('verify' => false) :
     * documents et réponses transitaient sans garantie d'authenticité du serveur. Un
     * dépôt de développement au certificat auto-signé peut s'en dispenser en
     * définissant _PLOOPI_S3_VERIFY_SSL à false, explicitement.
     *
     * @return S3Client
     */

    private static function client()
    {
        if (is_null(self::$objClient))
        {
            self::$objClient = new S3Client(self::options());
            self::$objClient->registerStreamWrapper();
        }

        return self::$objClient;
    }

    /**
     * Options du client S3, tirées de la configuration (_PLOOPI_S3_*)
     *
     * @return array
     */

    private static function options()
    {
        return [
            'version' => 'latest',
            'region' => _PLOOPI_S3_REGION,
            'bucket_endpoint' => false,
            'use_path_style_endpoint' => true,
            'endpoint' => _PLOOPI_S3_ENDPOINT_URL,
            'validate' => false,
            'http' => [
                'verify' => !defined('_PLOOPI_S3_VERIFY_SSL') || _PLOOPI_S3_VERIFY_SSL,
            ],
            'credentials' => [
                'key' => _PLOOPI_S3_AUTH_KEY,
                'secret' => _PLOOPI_S3_AUTH_SECRET,
            ]
        ];
    }

    /**
     * Indique si un objet existe
     *
     * @param string $filepath chemin « s3://dépôt/clé »
     * @return boolean
     */

    public static function file_exists(string $filepath): bool
    {
        self::client();

        return file_exists($filepath);
    }

    /**
     * Supprime un objet
     *
     * @param string $bucket dépôt
     * @param string $key clé
     * @return boolean
     */

    public static function delete(string $bucket, string $key): bool
    {
        try { self::client()->deleteObject(['Bucket' => $bucket, 'Key' => $key]); }
        catch (\Exception $objErreur) { return false; }

        return true;
    }

    /**
     * Déplace un objet (copie puis suppression de l'original)
     *
     * @param string $bucket dépôt
     * @param string $key clé d'origine
     * @param string $destfilename clé de destination
     * @return boolean
     */

    public static function copy(string $bucket, string $key, string $destfilename): bool
    {
        try {
            self::client()->copyObject([
                'Bucket' => $bucket,
                // CopySource est une URL : la clé doit y être encodée
                'CopySource' => $bucket.'/'.str_replace('%2F', '/', rawurlencode($key)),
                'Key' => $destfilename,
            ]);
        }
        catch (\Exception $objErreur) { return false; }

        return self::delete($bucket, $key);
    }

    /**
     * Copie un objet dans un fichier local
     *
     * @param string $bucket dépôt
     * @param string $key clé
     * @param string $destfilename fichier local de destination
     * @return boolean
     */

    public static function downloadfilelocally(string $bucket, string $key, string $destfilename): bool
    {
        try { self::client()->getObject(['Bucket' => $bucket, 'Key' => $key, 'SaveAs' => $destfilename]); }
        catch (\Exception $objErreur) { return false; }

        return file_exists($destfilename);
    }

    /**
     * Dépose un fichier local dans le dépôt.
     *
     * Appelait getObject (lecture) au lieu de putObject, et posait des étiquettes de
     * démonstration (isbn, id_article) : aucun document n'était déposé.
     *
     * @param string $bucket dépôt
     * @param string $key clé
     * @param string $originFile fichier local
     * @return boolean
     */

    public static function upload(string $bucket, string $key, string $originFile): bool
    {
        if (!file_exists($originFile)) return false;

        try { self::client()->putObject(['Bucket' => $bucket, 'Key' => $key, 'SourceFile' => $originFile]); }
        catch (\Exception $objErreur) { return false; }

        return true;
    }

    /**
     * Envoie un objet au navigateur.
     *
     * L'objet est rapatrié dans un fichier temporaire, puis servi par fs::downloadfile :
     * mêmes entêtes que pour un document local — nom de fichier échappé, type annoncé,
     * nosniff, pièce jointe imposée aux types qui s'exécuteraient dans le navigateur.
     * L'ancienne implémentation recopiait ses propres entêtes sans aucune de ces
     * protections.
     *
     * @param string $bucket dépôt
     * @param string $key clé
     * @param string $destfilename nom du fichier présenté au navigateur
     * @param boolean $deletefile true pour supprimer l'objet après envoi
     * @param boolean $attachment true pour une pièce jointe, false pour un affichage
     * @param boolean $die true pour terminer la requête
     * @return boolean false si l'objet est introuvable
     */

    public static function downloadfile(string $bucket, string $key, string $destfilename, bool $deletefile = false, bool $attachment = true, bool $die = true): bool
    {
        $strTmpPath = _PLOOPI_PATHDATA._PLOOPI_SEP.'tmp';
        fs::makedir($strTmpPath);
        $strTmpFile = $strTmpPath._PLOOPI_SEP.'s3_'.security::generatetoken(8);

        if (!self::downloadfilelocally($bucket, $key, $strTmpFile)) return false;

        fs::downloadfile($strTmpFile, $destfilename, true, $attachment, false);

        if ($deletefile) self::delete($bucket, $key);

        if ($die) system::kill(null, true);

        return true;
    }
}
