<?php
/**
 * Extension for YepnopeSilverStripe to hook into Controller
 */

class YepnopeControllerExtension extends Extension
{

    /**
     * Controller hook for evaluating Yepnope conditions after init() has been called
     * 
     * @return void
     */
    public function onAfterInit()
    {
        if (Yepnope::get_automatically_evaluate()) {
            Yepnope::eval_yepnope();
        }
    }
}
