<?php

    namespace App\Traits;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Http\Request;

    trait FilesTrait
    {
        public function saveFiles(Request $request, Model $model, $files) : string | null
        {
            foreach ($files as $file => $type) {
                try {
                    if ($request -> hasFile("$file")) {
                        $mediaPath    = $type . '_' . $model -> id . '_' . time() . '.' . $request -> file("$file") -> extension();
                        $relativePath = 'public/attachments';
                        $basePath     = '/' . $relativePath;
                        $request -> file($file) -> storeAs($basePath, $mediaPath);
                        $path            = $relativePath . '/' . $mediaPath;
                        $model ->{$type} = $path;
                        $model -> save();
                        return $path;
                    } else {
                        info('no file');
                    }
                } catch (\Exception $e) {
                    info($e->getMessage());
                    return null;
                }
            }
            return null;
        }
    }