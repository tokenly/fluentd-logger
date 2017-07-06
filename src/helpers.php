<?php

if (!function_exists('fluent_measure')) {

    /**
     * logs a measurement to fluentd
     * A Laravel helper
     * 
     * @param  string $event a measurement name like widgets.created
     * @param  array  $data  an array of numeric data.  All entries in this array must be an integer or float.
     * @param  array  $tags  an array of tags.  All tags are lookup fields such as userId or widgetType.
     */
    function fluent_measure($event, $data=[], $tags=null) {
        app('fluent.measurements')->log($event, $data, $tags);
    }

}
