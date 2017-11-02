<?php

namespace RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\Manipulator;

use Sensio\Bundle\GeneratorBundle\Manipulator\Manipulator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class ServiceConfigurationManipulator extends Manipulator
{
    protected $filePath;

    protected $encoder;

    protected $filename;

    protected $extension;

    public function __construct($filePath)
    {
        $pathInfo = pathinfo($filePath);
        $this->filePath = $filePath;
        $this->filename = $pathInfo['filename'];
        $this->extension = $pathInfo['extension'];
        $this->encoder = new XmlEncoder();
    }

    public function addServiceConfiguration($namespace, $readModelName)
    {
        $data = $this->encoder->decode(file_get_contents($this->filePath), $this->extension);
        $serviceId = sprintf('%s.readmodel', strtolower($readModelName));
        $data['services'] = isset($data['services']) ? $data['services'] : [];
        $data['services']['service'] = isset($data['services']['service']) ? $data['services']['service'] : [];
        if (!array_search($serviceId, array_column($data['services']['service'], '@id'))) {
            $data['services']['service'][] = $this->getNewServiceData($namespace, $readModelName, $serviceId);
        } else {
            throw new \RuntimeException(sprintf("Service id %s is already set in %s", $serviceId, $this->filePath));
        }
        $data = $this->removeEmptyText($data);
        $data = $this->encoder->encode($data, $this->extension, ['xml_format_output' => true]);

        file_put_contents($this->filePath, $data);
    }

    private function getNewServiceData($namespace, $readModelName, $serviceId)
    {
        return [
            '@id'    => $serviceId,
            '@class' => 'Broadway\ReadModel\ReadModel',
            'factory' => [
                '@method'  => 'create',
                '@service' => 'broadway.read_model.repository_factory'
            ],
            'argument' => [
                [
                    '#' => $serviceId,
                ],
                [
                    '#' => sprintf('%s\\ReadModel\\%sReadModel', $namespace, $readModelName)
                ]
            ]
        ];
    }

    private function removeEmptyText($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $d) {
                if ($key === '#' && empty($d)) {
                    unset($data[$key]);
                } else {
                    $data[$key] = $this->removeEmptyText($data[$key]);
                }
            }
        }

        return $data;
    }
}