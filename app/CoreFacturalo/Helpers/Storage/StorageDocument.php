<?php

namespace App\CoreFacturalo\Helpers\Storage;

use Illuminate\Support\Facades\Storage;

trait StorageDocument
{
    protected $folder;
    protected $filename;

    public function uploadStorage($filename, $file_content, $file_type, $root = null)
    {
        $this->setData($filename, $file_type, $root);
        Storage::disk('tenant')->put($this->folder.DIRECTORY_SEPARATOR.$this->filename, $file_content);
    }

    public function downloadStorage($filename, $file_type, $root = null)
    {
        $this->setData($filename, $file_type, $root);
        return Storage::disk('tenant')->download($this->folder.DIRECTORY_SEPARATOR.$this->filename);
    }

    public function getStorage($filename, $file_type, $root = null)
    {
        $this->setData($filename, $file_type, $root);
        return Storage::disk('tenant')->get($this->folder.DIRECTORY_SEPARATOR.$this->filename);
    }

    private function setData($filename, $file_type, $root)
    {
        $extension = 'xml';
        switch ($file_type) {
            case 'unsigned':
                break;
            case 'signed':
                break;
            case 'pdf':
                $extension = 'pdf';
                break;
            case 'cdr':
                $filename = 'R-'.$filename;
                $extension = 'zip';
                break;
        }
        $this->filename = $filename.'.'.$extension;
        $this->folder = ($root)?$root.DIRECTORY_SEPARATOR.$file_type:$file_type;
    }
}