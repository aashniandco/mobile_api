<?php
namespace Eleadtech\AlphabetOption\Cron;
/**
 * Description of Api
 *
 */
class UpdatePosition
{
    protected $alphabetOptionService;
    public function __construct(
        \Eleadtech\AlphabetOption\Service\AlphabetOption $alphabetOptionService
    ){
        $this->alphabetOptionService = $alphabetOptionService;
    }
    public function execute()
    {
        try{
            $this->alphabetOptionService->updatePosition();
        } catch (\Exception $ex) {
            $this->alphabetOptionService->writeLog($ex->getMessage());
        }
        return $this;
    }

}
