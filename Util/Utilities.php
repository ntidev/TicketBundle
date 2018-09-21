<?php

namespace NTI\TicketBundle\Util;


use NTI\TicketBundle\Entity\Ticket\Document;
use NTI\TicketBundle\Util\Rest\RestResponse;
use Symfony\Component\HttpFoundation\Request;

class Utilities
{


    /**
     * https://gist.github.com/dahnielson/508447
     *
     * Generate v4 UUID
     *
     * Version 4 UUIDs are pseudo-random.
     */
    public static function v4UUID()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * @param array $data
     * @param array $keys
     * @return array
     */
    public static function arrayFilterByKeys ($data = array(), $keys = array()){
        $filter = array_filter($data, function ($key) use ($keys) {
            return in_array($key, $keys);
        }, ARRAY_FILTER_USE_KEY);
        return $filter;
    }

    /**
     * @param Request $request
     * @param null $directory
     * @return RestResponse|object
     */
    public static function fileUpload(Request $request, $directory = null){

        if (!$directory)
            return new RestResponse(null, 400, "Upload directory is invalid.");

        # -- dependencies
        $validFormats = Document::ALLOWED_FORMATS;
        $uploadDir = $directory;

        # -- Get posted information
        $name = $request->get('name');
        $filename = preg_replace('/[^-\w\.]+/i', "", $request->get('filename'));
        $type = preg_replace('/[^-\w\.\/]+/i', "", $request->get('type'));
        $data = $request->get('data');

        # -- Pre-validate data
        if ($name == "") return new RestResponse(null, 400, "The Document Name is required.");
        if ($filename == "") return new RestResponse(null, 400, "The Document Filename is invalid.");
        if ($type == "") return new RestResponse(null, 400, "Invalid Document format. Allowed formats are: " . implode(", ", $validFormats));
        if ($data == "") return new RestResponse(null, 400, "Unable to get the document content. Please refresh and try again.");

        # -- Guess extension
        $pathInfo = pathinfo($filename);
        $extension = strtoupper($pathInfo['extension']);
        if ($extension == "" || !in_array($extension, $validFormats))
            return new RestResponse(null,400,"Invalid Document format. Allowed formats are: " . implode(", ", $validFormats));

        # -- Strip the content from the data
        $base64delimiterPosition = strpos($data, 'base64,');
        if ($base64delimiterPosition) {
            $data = substr_replace($request->get('data'), "", 0, $base64delimiterPosition + 7);
        }
        # -- return if data is invalid
        if ($data == "") return new RestResponse(null, 400,"Invalid Document Data. Data must be provided in a base64 encoded format.");

        # -- temp dir exist ?
//        if (!file_exists($this->tmpDir)) {
//            if (!mkdir($this->tmpDir, 0777, true)) {
//                return new RestResponse(null, 500,"An error occurred uploading the document to the server.");
//            }
//        }

        # -- filename config
        $hash = sha1("1" . time() . $filename);
        $filename = $hash . "_" . $filename;

//        # -- saving in the tmp dir for further validation
//        $tmpFilePath = $this->tmpDir . "/" . $filename;
//        if (!fwrite(fopen($tmpFilePath, "w+"), base64_decode($data))) {
//            return new RestResponse(null, 500,"An error occurred uploading the document.");
//        }
//
//        # -- file size validation
//        $size = filesize($tmpFilePath);
//        if (($size/1000000) > $this->maxSize)
//            return new RestResponse(null, 400, "The file you are trying to upload is to large. File size must be less than ".$this->maxSize."MB.");
//
//        # -- delete temp file
//        @unlink($tmpFilePath);

        // Prepare upload folder
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                return new RestResponse(null, 500,"Unable to create or write in the upload directory provided.");
            }
        }

        // Write the content to the file (replace if exists)
        $filePath = $uploadDir . "/" . $filename;
        if (!fwrite(fopen($filePath, "w+"), base64_decode($data))) {
            return new RestResponse(null, 500, "An error occurred uploading the document.");
        }

        $uploadedDocumentInfo = array(
            "name" => $name,
            "filename" => $filename,
            "type" => $type,
            "format" => $extension,
            "size" => $size = filesize($filePath),
            "directory" => $directory,
            "path" => $uploadDir,
            "hash" => $hash,
            "filePath" => $filePath,
        );

        return (object) $uploadedDocumentInfo;

    }

}