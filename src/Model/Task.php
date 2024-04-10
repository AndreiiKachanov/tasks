<?php

namespace App\Model;

use Exception;

/**
 * Model for working with tasks
 */
class Task extends BaseModel
{
    public static string $tableName = TABLE_PREFIX . 'tasks';

    private static $instance;

    public function __construct()
    {
        parent::__construct(self::$tableName, 'id');
    }

    /**
     * Singleton
     *
     * @return Task
     */
    public static function instance(): Task
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get task by id
     *
     * @param int $id
     * @return array
     */
    public function get(int $id): array
    {
        return parent::get($id);
    }


    /**
     * Add a task
     *
     * @param array $fields
     * @return false|string
     * @throws Exception
     */
    public function add(array $fields): false|string
    {
            // Prepare data for insertion into the database
            $dataToInsert = $this->prepareDataToInsert($fields);
            // Add to the database - get the id of the added record or false
            $taskId = parent::add($dataToInsert);

        if (!$taskId) {
            return false;
        }

        try {
            // If a file is attached - check if it is a valid image
            if (isset($fields['file']) && $fields['file']['size'] > 0) {
                $file = $fields['file'];

                // If the image is not valid - errors will be recorded in $this->errors[]
                if (!$this->validateImage($file)) {
                    $this->delete($taskId);
                    return false;
                }
                // If the image is saved - update the record in the database
                if ($newFileName = $this->saveImage(IMG_DIR, $file)) {
                    return parent::edit($taskId, ['img' => $newFileName]);
                }
            }
        } catch (Exception $e) {
            $this->delete($taskId);
            throw new Exception('Error adding a task. ' . $e->getMessage());
        }
        return true;
    }

    /**
     * Edit a task
     *
     * @param string|int $id
     * @param array $fields
     * @return bool
     * @throws Exception
     */
    public function edit(string|int $id, array $fields): bool
    {
        return parent::edit($id, $fields);
    }

    /**
     * @param string|int $id
     * @return int
     * @throws Exception
     */
    public function delete(string|int $id): int
    {
        return parent::delete($id);
    }

    /**
     * Save an image
     *
     * @param string $imgDir
     * @param array $file
     * @return string
     * @throws Exception
     */
    public function saveImage(string $imgDir, array $file): string
    {
        $newFileName = randomStr() . '.' . getFileExtension($file['name']);
        $newFileDest = $imgDir . $newFileName;

        if (copy($file['tmp_name'], $newFileDest) === false) {
            throw new Exception('File copy error. ' . error_get_last()['message']);
        }

        $size = getimagesize($newFileDest);
        if ($size[0] > IMG_SMALL_WIDTH || $size[1] > IMG_SMALL_HEIGHT) {
            // Resize the image
            $this->resizeImage($newFileDest, IMG_SMALL_WIDTH, IMG_SMALL_HEIGHT);
        }

        return $newFileName;
    }

    /**
     * Image Validation
     *
     * @param array $file
     * @return bool
     */
    private function validateImage(array $file): bool
    {
        $fileExt = getFileExtension($file['name']);
        $whiteList = ['jpg', 'JPG', 'png', 'PNG', 'gif', 'GIF'];

        if (!in_array(strtolower($fileExt), $whiteList)) {
            // Record errors in the array
            $this->errors[] = 'Пожалуйста, загрузите файл в одном из следующих форматов: JPG, JPEG, PNG, GIF.';
            return false;
        }

        // 5 * 1024 * 1024 =  5 MB
        if (($file['size'] > 5 * 1024 * 1024)) {
            // 'File exceeds 5 MB';
            $this->errors[] = 'Размер загружаемого файла не должен превышать 5 Мб.';
            return false;
        }
        return true;
    }

    /**
     * Prepare data for insertion into the database
     *
     * @param array $fields
     * @return array
     * @throws Exception
     */
    private function prepareDataToInsert(array $fields): array
    {
        if (($user = User::instance()->get()) !== null) {
            $dataToInsert['id_user'] = $user['id_user'];
        }

        $dataToInsert['author'] = $fields['author'];
        $dataToInsert['email'] = $fields['email'];
        $dataToInsert['content'] = $fields['content'];

        return $dataToInsert;
    }

    /**
     * Resize image
     *
     * @param string $dest
     * @param int $width
     * @param int $height
     * @param int $rgb
     * @param int $quality
     * @return bool
     * @throws Exception
     */
    public function resizeImage(string $dest, int $width, int $height, int $rgb = 0xFFFFFF, int $quality = 100): bool
    {
        try {
            if (!file_exists($dest)) {
                throw new Exception('Source image not found.');
            }
            $size = getimagesize($dest);

            if ($size === false) {
                throw new Exception('Failed to get image size.');
            }

            // Determine the original format based on the MIME information provided
            // by the getimagesize function, and choose the corresponding format
            // imagecreatefrom function.
            $format = strtolower(substr($size['mime'], strpos($size['mime'], '/') + 1));

            $imgCreateFromFunc = 'imagecreatefrom' . $format;
            if (!function_exists($imgCreateFromFunc)) {
                throw new Exception("Unsupported image create function for format: $format");
            }

            $xRatio = $width / $size[0];
            $yRatio = $height / $size[1];

            $ratio = min($xRatio, $yRatio);
            $useXRatio = ($xRatio === $ratio);

            $newWidth   = $useXRatio  ? $width  : floor($size[0] * $ratio);
            $newHeight  = !$useXRatio ? $height : floor($size[1] * $ratio);
            $newLeft    = $useXRatio  ? 0 : floor(($width - $newWidth) / 2);
            $newTop     = !$useXRatio ? 0 : floor(($height - $newHeight) / 2);

            $imgSrc = $imgCreateFromFunc($dest);
            $imgDest = imagecreatetruecolor($width, $height);

            imagefill($imgDest, 0, 0, $rgb);
            imagecopyresampled(
                $imgDest,
                $imgSrc,
                $newLeft,
                $newTop,
                0,
                0,
                $newWidth,
                $newHeight,
                $size[0],
                $size[1]
            );

            imagejpeg($imgDest, $dest, $quality);

            imagedestroy($imgSrc);
            imagedestroy($imgDest);

            return true;
        } catch (Exception $e) {
            throw new Exception('Error resizing image: ' . $e->getMessage());
        }
    }
}
