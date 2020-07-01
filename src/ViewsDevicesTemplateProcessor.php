<?php namespace EvolutionCMS\ViewsDevices;

use EvolutionCMS\Models\SiteTemplate;
use EvolutionCMS\TemplateProcessor;

class ViewsDevicesTemplateProcessor extends TemplateProcessor
{
    private $configName = 'ViewsDevicesTemplateNamespace';
    private $templateAlias;
    private $md;

    public function getBladeDocumentContent()
    {
        $template = false;
        $doc = $this->core->documentObject;
        if ($doc['template'] == 0) return $template;
        if (EvolutionCMS()->getConfig('enable_cache')) {
            $key = 'templates_id_alias';
            if (!$templates = \Cache::get($key)) {
                $templates = SiteTemplate::all()->pluck('templatealias','id')->toArray();
                \Cache::forever($key, $templates);
            }
            $this->setTemplateAlias($templates[$doc['template']]);
        } else {
            $this->setTemplateAlias(SiteTemplate::select('templatealias')->find($doc['template'])->templatealias);
        }

        $this->md = $MD = new \Mobile_Detect;
        $folderDevice = $this->getFolderDevice();

        switch (true) {
            case $this->core['view']->exists($folderDevice . 'tpl-' . $doc['template'] . '_doc-' . $doc['id']):
                $template = $folderDevice . 'tpl-' . $doc['template'] . '_doc-' . $doc['id'];
                break;
            case $this->core['view']->exists($folderDevice . 'doc-' . $doc['id']):
                $template = $folderDevice . 'doc-' . $doc['id'];
                break;
            case $this->core['view']->exists($folderDevice . 'tpl-' . $doc['template']):
                $template = $folderDevice . 'tpl-' . $doc['template'];
                break;
            case $this->core['view']->exists($folderDevice . $this->templateAlias):
                $classDir = str_replace('/', '\\', ucfirst($folderDevice));
                $baseClassName = $this->core->getConfig($this->configName) . $classDir . 'BaseController';
                if (class_exists($baseClassName)) {
                    $classArray = explode('.', $this->templateAlias);
                    $classArray = array_map(function ($item) {
                        return ucfirst(trim($item));
                    }, $classArray);
                    $classViewPart = implode('.', $classArray);
                    $className = $this->dashesToCamelCase(str_replace('.', '\\', $classViewPart));
                    $className = $this->core->getConfig($this->configName) . $classDir . ucfirst($className) . 'Controller';
                    if (!class_exists($className)) {
                        $className = $baseClassName;
                    }
                    $customClass = new $className();
                }
                $template = $folderDevice . $this->templateAlias;
                break;
            default:
                $content = $doc['template'] ? $this->core->documentContent : $doc['content'];
                if (!$content) {
                    $content = $doc['content'];
                }
                if (strpos($content, '@FILE:') === 0) {
                    $template = str_replace('@FILE:', '', trim($content));
                    if (!$this->core['view']->exists($template)) {
                        $this->core->documentObject['template'] = 0;
                        $this->core->documentContent = $doc['content'];
                    }
                }
        }
        return $template;
    }

    private function setTemplateAlias($alias)
    {
        if (!$this->templateAlias)
        {
            $this->templateAlias = $alias;
        }
    }

    private function getFolderDevice($tablet = true, $mobile = true)
    {
        switch (true) {
            case $this->md->isTablet() && $tablet:
                $dir = 'tablet/';
                if (!file_exists(MODX_BASE_PATH . 'views/' . $dir))
                    $dir = $this->getFolderDevice(false);
                break;
            case $this->md->isMobile() && $mobile:
                $dir = 'mobile/';
                if (!file_exists(MODX_BASE_PATH . 'views/' . $dir))
                    $dir = $this->getFolderDevice(false, false);
                break;
            default:
                $dir = 'desktop/';
        }
        if (!file_exists(MODX_BASE_PATH . 'views/' . $dir))
            $dir = '';

        return $dir;
    }

    private function dashesToCamelCase($string)
    {
        return str_replace('-', '', ucwords($string, '-'));
    }
}
