<?php

namespace Apps\Tms\Components\System\Storages;

use Apps\Core\Packages\Adminltetags\Traits\DynamicTable;
use System\Base\BaseComponent;

class StoragesComponent extends BaseComponent
{
    use DynamicTable;

    protected $storages;

    public function initialize()
    {
        $this->setModuleSettings();

        $this->storages = $this->usePackage('storages');
    }

    /**
     * @acl(name=view)
     */
    public function viewAction()
    {
        if (isset($this->getData()['uuid']) && $this->getData()['uuid'] !== '') {
            return $this->storages->getFile($this->getData());
        }

        if ($this->app['id'] == 1) {
            if (isset($this->getData()['id'])) {
                if ($this->getData()['id'] != 0) {
                    $storage = $this->storages->getById($this->getData()['id']);

                    if (!$storage) {
                        return $this->throwIdNotFound();
                    }

                    if (is_string($storage['allowed_image_mime_types'])) {
                        $storage['allowed_image_mime_types'] = $this->helper->decode($storage['allowed_image_mime_types']);
                    }
                    if (is_string($storage['allowed_image_sizes'])) {
                        $storage['allowed_image_sizes'] = $this->helper->decode($storage['allowed_image_sizes']);
                    }
                    if (is_string($storage['allowed_file_mime_types'])) {
                        $storage['allowed_file_mime_types'] = $this->helper->decode($storage['allowed_file_mime_types']);
                    }

                    $this->view->storage = $storage;

                    $this->view->storageType = $storage['type'];
                } else {
                    $this->view->storageType = $this->getData()['type'];
                }

                $storagePackage = $this->modules->packages->getPackageByName('Storages');

                if (is_string($storagePackage['settings'])) {
                    $storagePackage['settings'] = $this->helper->decode($storagePackage['settings'], true);
                }

                $this->view->allowedImageMimeTypes = $storagePackage['settings']['allowedImageMimeTypes'];
                $this->view->allowedImageSizes = $storagePackage['settings']['allowedImageSizes'];
                $this->view->allowedFileMimeTypes = $storagePackage['settings']['allowedFileMimeTypes'];

                $maxFilesizeSettings = ini_get('upload_max_filesize');
                $maxFilesize = toBytes($maxFilesizeSettings);
                $maxPostsizeSettings = ini_get('post_max_size');
                $maxPostsize = toBytes($maxPostsizeSettings);

                if ($maxPostsize >= $maxFilesize) {
                    $this->view->maxBytes = $maxFilesize;
                    $this->view->settingSize = 'upload_max_filesize = ' . $maxFilesizeSettings;
                } else {
                    $this->view->maxBytes = $maxPostsize;
                    $this->view->settingSize = 'post_max_size = ' . $maxPostsizeSettings;
                }

                $this->view->responseCode = $this->storages->packagesData->responseCode;

                $this->view->responseMessage = $this->storages->packagesData->responseMessage;

                $this->view->pick('storages/view');

                return;
            }

            $this->view->storageType = '';

            $controlActions =
                [
                    'actionsToEnable'       =>
                    [
                        'edit'      => 'system/storages',
                        'remove'    => 'system/storages/remove'
                    ]
                ];

            $this->generateDTContent(
                $this->storages,
                'system/storages/view',
                null,
                ['name', 'type', 'permission'],
                true,
                ['name', 'type', 'permission'],
                $controlActions,
                null,
                null,
                'name'
            );

            $this->view->pick('storages/list');

            return;
        }

        return false;
    }

    /**
     * @acl(name=add)
     */
    public function addAction()
    {
        $this->requestIsPost();

        if (isset($this->postData()['upload']) && $this->postData()['upload'] == true) {
            if ($this->request->hasFiles()) {
                if ($this->storages->storeFile()) {
                    $this->view->responseData = $this->storages->packagesData->responseData;
                }

                $this->addResponse(
                    $this->storages->packagesData->responseMessage,
                    $this->storages->packagesData->responseCode
                );
            } else {
                $this->addResponse('No files provided to upload or file provided cannot be uploaded due to error.', 1);
            }

            return;
        }

        $this->storages->addStorage($this->postData());

        $this->addResponse(
            $this->storages->packagesData->responseMessage,
            $this->storages->packagesData->responseCode
        );
    }

    /**
     * @acl(name=update)
     */
    public function updateAction()
    {
        $this->requestIsPost();

        $this->storages->updateStorage($this->postData());

        $this->addResponse(
            $this->storages->packagesData->responseMessage,
            $this->storages->packagesData->responseCode
        );
    }

    /**
     * @acl(name=remove)
     */
    public function removeAction()
    {
        $this->requestIsPost();

        if (isset($this->postData()['uuid'])) {
            $this->storages->removeFile($this->postData()['uuid']);

            $this->addResponse(
                $this->storages->packagesData->responseMessage,
                $this->storages->packagesData->responseCode
            );

            return;
        }

        if ($this->app['id'] == '1') {
            $this->storages->removeStorage($this->postData());

            $this->addResponse(
                $this->storages->packagesData->responseMessage,
                $this->storages->packagesData->responseCode
            );
        }
    }
}
