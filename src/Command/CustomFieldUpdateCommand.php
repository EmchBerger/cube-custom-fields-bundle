<?php

namespace CubeTools\CubeCustomFieldsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Refreshes the leaves of all accessRights based on the current accessGroup configuration
 *
 */
class CustomFieldUpdateCommand extends ContainerAwareCommand
{
    private $output; // OutputInterface

    protected function configure()
    {
        $this
            ->setName('cube:customfield:update')
            ->setDescription('Updates the string representation of the custom fields table entries.')
        ;
    }

    /**
     * Cleans up access rights
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return boolean
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        // disable the CustomFieldLinkedEntityListener during the execution of the command (to prevent loops)
        $listenerInst = null;
        foreach ($em->getEventManager()->getListeners() as $event => $listeners) {
            foreach ($listeners as $hash => $listener) {
                //dump($listener);
                if ($listener instanceof \CubeTools\CubeCustomFieldsBundle\EventListener\CustomFieldLinkedEntityListener) {
                    $listenerInst = $listener;
                    break 2;
                }
            }
        }
        $evm = $em->getEventManager();
        $evm->removeEventListener(array('onFlush'), $listenerInst);

        // datetime custom fields
        $datetimeCf = $em->getRepository('CubeCustomFieldsBundle:DatetimeCustomField')->findAll();
        $this->updateCfEntities($datetimeCf);
        $em->flush();

        // text custom fields
        $textCf = $em->getRepository('CubeCustomFieldsBundle:TextCustomField')->findAll();
        $this->updateCfEntities($textCf);
        $em->flush();

        // textarea custom fields
        $textareaCf = $em->getRepository('CubeCustomFieldsBundle:TextareaCustomField')->findAll();
        $this->updateCfEntities($textareaCf);
        $em->flush();

        // entity custom fields
        $entityCf = $em->getRepository('CubeCustomFieldsBundle:EntityCustomField')->findAll();
        $this->updateCfEntities($entityCf);
        $em->flush();
    }

    private function updateCfEntities($entities)
    {
        foreach ($entities as $entity) {
            $entity->storeStrRepresentation();
        }
    }
}
