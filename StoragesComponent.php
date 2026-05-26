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
    }

    /**
     * @acl(name=add)
     */
    public function addAction()
    {
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
        }
    }

    public function updateAction()
    {
        //
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
        }
    }
}
