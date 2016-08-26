<?php

/**
 * Represents a set of widgets shown on a page.
 *
 * @package widgets
 */
class WidgetArea extends DataObject
{
    /**
     * @var array
     */
    private static $has_many = array(
        "Widgets" => "Widget"
    );

    /**
     *
     * @var string
     */
    public $template = __CLASS__;
    
    /**
     * Used in template instead of {@link Widgets()} to wrap each widget in its 
     * controller, making it easier to access and process form logic and 
     * actions stored in {@link WidgetController}.
     * 
     * @return SS_List - Collection of {@link WidgetController} instances.
     */
    public function WidgetControllers()
    {
        $controllers = new ArrayList();
        $items = $this->ItemsToRender();
        if (!is_null($items)){
            foreach ($items as $widget) {
                $controller = $widget->getController();

                $controller->init();
                $controllers->push($controller);
            }
        }
        return $controllers;
    }

    /**
     * @return HasManyList
     */
    public function Items()
    {
        return $this->getComponents('Widgets');
    }

    /**
     * @return HasManyList
     */
    public function ItemsToRender()
    {
        return $this->getComponents('Widgets')
            ->filter("Enabled", 1);
    }

    /**
     * @return string - HTML
     */
    public function forTemplate()
    {
        return $this->renderWith($this->template);
    }

    /**
     *
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * Delete all connected Widgets when this WidgetArea gets deleted
     */
    public function onBeforeDelete()
    {
        parent::onBeforeDelete();
        foreach ($this->Widgets() as $widget) {
            $widget->delete();
        }
    }

    public function doPublish()
    {
        $widgetIDs = array();
        foreach ($this->Items() as $widget) {
            $widget->doPublish();
            $widgetIDs[] = $widget->ID;
        }
        $liveWidgets = Versioned::get_by_stage('Widget', 'Live')->filter('ParentID', $this->ID);
        foreach ($liveWidgets as $widget) {
            if (!in_array($widget->ID, $widgetIDs)) {
                $widget->deleteFromStage('Live');
            }
        }
    }
}
