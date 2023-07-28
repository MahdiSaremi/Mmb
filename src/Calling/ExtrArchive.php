<?php
#auto-name
namespace Mmb\Calling;

use ZipArchive;

class ExtrArchive
{

    private static $ignores = [];

    private static function setIgnores(array $ignores)
    {
        $ignores = array_map(fn($ig) => strtolower($ig), $ignores);
        static::$ignores = $ignores;
    }
    
    public static function archiveFiles(string $output, string $directory, string $extend = 'php', array $ignores = [])
    {
        // Proccess on settings
        $ignores[] = $output;
        static::setIgnores($ignores);

        // Find all files
        echo "\r@ Please wait...\tN/A\t[ 0% ]";
        $files = [];
        $folders = [];
        static::findAllFiles($directory, $extend, $files, $folders);
        $count = count($files);

        // Start file
        $out = fopen($output, 'w');
        fwrite($out, '<?php
        if(defined("IGNORE_EXTRACT_ME")) return;
        (function() {
            $me = fopen(__FILE__, "r");
            $point = strpos(fread($me, 2000), "__halt_"."compiler();?>") + 20;
            fseek($me, $point);
            while(true) {
                $len = unpack("N", base64_decode(fread($me, 8)))[1];
                if($len == 0) break;
                $name = fread($me, $len);
                @mkdir(__DIR__ . "/" . $name);
            }
            while(true) {
                $len = @unpack("N", base64_decode(fread($me, 8)))[1];
                if($len == 0) break;
                $name = fread($me, $len);
                $len = unpack("N", base64_decode(fread($me, 8)))[1];
                $file = fopen(__DIR__ . "/" . $name, "w");
                if(!$file)
                    die($name);
                stream_copy_to_stream($me, $file, $len);
                fclose($file);
            }
            fclose($me);
            file_put_contents(__FILE__, "<?php /* Extracted :) */ ?>");
        })();
        __halt_compiler();?>');

        echo "\r@ Archiving...\t0/$count\t[ 0% ]";


        // Folders
        foreach($folders as $folder)
        {
            $folderName = substr($folder, strlen($directory) + 1);
            if($folderName)
            {
                fwrite($out, base64_encode(pack("N", strlen($folderName))));
                fwrite($out, $folderName);
            }
        }
        fwrite($out, base64_encode(pack("N", 0)));

        // Files
        foreach($files as $i => $file)
        {
            $fileName = substr($file, strlen($directory) + 1);
            $progress = round($i / $count * 100);
            echo "\r@ Archiving...\t$i/$count\t[ $progress% ]";

            fwrite($out, base64_encode(pack("N", strlen($fileName))));
            fwrite($out, $fileName);

            fwrite($out, base64_encode(pack("N", filesize($file))));
            $f = fopen($file, "r");
            stream_copy_to_stream($f, $out);
            fclose($f);
        }

        echo "\r@ Archiving...\t$count/$count\t[ 100% ]";

        // Close file
        fclose($out);
        echo "\r~ Archive completed [ 100% ] ... Saved as $output";

    }

    private static function findAllFiles($dir, $ext, &$files, &$folders)
    {
        if(!is_array($dir)) $dir = [$dir];
        
        foreach($dir as $folder)
        {
            if(in_array(strtolower($folder), static::$ignores)) continue;
            
            $folders[] = $folder;
            $list = glob("$folder/**" . ($ext ? ".$ext" : null));
            $list = array_filter($list, fn($x) => !in_array(strtolower($x), static::$ignores));
            array_push($files, ...array_filter($list, fn($file) => is_file($file)));
            $intoDirs = glob("$folder/**", GLOB_ONLYDIR);
            if($intoDirs)
                static::findAllFiles($intoDirs, $ext, $files, $folders);
        }
    }

    public static function zipBackup(string $backupFolder, string $backupAs, string $directory, array $ignores = [])
    {
        @mkdir($backupFolder);
        if(class_exists('ZipArchive'))
        {
            $zip = new ZipArchive;
            if($zip->open($saveAs = $backupFolder . '/' . jdate($backupAs, tr_num: 'en') . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE))
            {

                static::setIgnores($ignores);
                $files = $folders = [];
                static::findAllFiles($directory, null, $files, $folders);

                foreach($folders as $folder)
                {
                    $zip->addEmptyDir(getRelPath($folder, $directory));
                }

                foreach($files as $file)
                {
                    $zip->addFile($file, getRelPath($file, $directory));
                }
                
                $zip->close();
                echo "\n~ Backup saved as $saveAs";
            }

        }
        else
        {
            echo "\n~ Error: ZipArchive extention is not enabled";
        }
    }

}
