<?php

defined('BASEPATH') or exit('No direct script access allowed');
use League\Flysystem\Filesystem;
use League\Flysystem\PhpseclibV3\SftpConnectionProvider;
use League\Flysystem\PhpseclibV3\SftpAdapter;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Sabre\DAV\Client;
use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
/** @var Aws\S3\S3ClientInterface $client */
class Flexibackup_remote_module
{
    private $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
    }

    public function send_backup_to_remote_storage($file,$backup)
    {
        try{
            return $this->send($file,$backup);
        }catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private function send($file,$backup)
    {
        $remote_storage = get_option('flexibackup_remote_storage');
        //adapter variable is a type of League\Flysystem\Filesystem
        switch ($remote_storage) {
            case 'ftp':
                 try{return $this->ftp_client($file);}catch (Exception $e){throw new Exception($e->getMessage());}
                break;
            case 's3':
                 try{return $this->s3_client($file);}catch (Exception $e){throw new Exception($e->getMessage());}
                break;
            case 'sftp':
                 try{return $this->sftp_client($file);}catch (Exception $e){throw new Exception($e->getMessage());}
            case 'webdav':
                 try{return $this->webdav_client($file);}catch (Exception $e){throw new Exception($e->getMessage());}
            case 'email':
                  try{return $this->email_client($file,$backup);}catch (Exception $e){throw new Exception($e->getMessage());}
            default:
                return false;
        }
    }

    private function ftp_client($file){
        $adapter = new League\Flysystem\Ftp\FtpAdapter(
            League\Flysystem\Ftp\FtpConnectionOptions::fromArray([
                'host' => get_option('flexibackup_ftp_server'), // required
                'root' => get_option('flexibackup_ftp_path'), // required
                'username' => get_option('flexibackup_ftp_user') , // required
                'password' => get_option('flexibackup_ftp_password'), // required
                'port' => 21,
                'ssl' => false,
                'timeout' => 90,
                'utf8' => false,
                'passive' => true,
                'transferMode' => FTP_BINARY,
                'systemType' => null, // 'windows' or 'unix'
                'ignorePassiveAddress' => null, // true or false
                'timestampsOnUnixListingsEnabled' => false, // true or false
                'recurseManually' => true // true
            ])
        );
        $filesystem =  new League\Flysystem\Filesystem($adapter);
        try{
            return $this->write_file_to_remote($filesystem, $file);
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    private function s3_client($file){
        $client = new S3Client([
            'version' => 'latest',
            'region'  => get_option('flexibackup_s3_region'),
            'credentials' => [
                'key'    => get_option('flexibackup_s3_access_key'),
                'secret' => get_option('flexibackup_s3_secret_key'),
            ],
        ]);

        // The internal adapter
        $adapter =  new AwsS3V3Adapter(
            $client,
            // Bucket name
            get_option('flexibackup_s3_location'),
        );
        $filesystem =  new League\Flysystem\Filesystem($adapter);
        try{
            return $this->write_file_to_remote($filesystem, $file);
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    private function sftp_client($file)
    {
        $filesystem = new Filesystem(new SftpAdapter(
            new SftpConnectionProvider(
                get_option('flexibackup_sftp_server'), // host (required)
                get_option('flexibackup_sftp_user'), // username (required)
                get_option('flexibackup_sftp_password'), // password (optional, default: null) set to null if privateKey is used
            ),
            get_option('flexibackup_sftp_path'), // root path (required)
        ));
        try{
            return $this->write_file_to_remote($filesystem, $file);
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    private function webdav_client($file)
    {
        $client = new Client([
            'baseUri' => get_option("flexibackup_webdav_base_uri"),
            'userName' => get_option("flexibackup_webdav_username"),
            'password' => get_option("flexibackup_webdav_password")
        ]);
        $adapter = new WebDAVAdapter($client);
        $filesystem =  new Filesystem($adapter);
        try{
            return $this->write_file_to_remote($filesystem, $file);
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    private function email_client($file_path,$backup)
    {
        $staff_email = get_option("flexibackup_email_address");
        //check if we have email
        if(!$staff_email) {
            //throw exceptoin
            throw new Exception("Email address is not provided");
        }
        //get file extension
        $file_extension = pathinfo($file_path, PATHINFO_EXTENSION);

        //get file name without extension
        $file_name = pathinfo($file_path, PATHINFO_FILENAME);
        $file = [
            'attachment' => $file_path,
            'filename'   => $file_name,
            'type'       => $file_extension,
            'read'       => true,
        ];
        $template_name = 'Flexibackup_new_backup_to_staff';
        $staffId = get_staff_user_id();
        $template = mail_template($template_name,"flexibackup",$staff_email,$staffId,$backup,$file);
        if ($template->send()) {
            return true;
        }
        return false;
    }

    private function write_file_to_remote($filesystem, $file)
    {
        try {
            $fileName = basename($file);
            $parts = explode('/', $file);
            $directory = $parts[count($parts) - 2];
            $remote_location = $directory.'/'.$fileName;
            // Use the write method to upload the zip file to S3 with the original file name
            //if file has .sql extension
            if (strpos($file, '.sql') !== false) {
                $filesystem->write($remote_location, file_get_contents($file));
            }else{
                //it is zip file
                $filesystem->writeStream($remote_location, fopen($file, 'r+'));
            }
            return true;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }


}