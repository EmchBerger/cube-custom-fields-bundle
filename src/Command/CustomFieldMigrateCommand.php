<?php

namespace CubeTools\CubeCustomFieldsBundle\Command;

use CubeTools\CubeCustomFieldsBundle\Utils\ConfigReader;
use CubeTools\CubeCustomFieldsBundle\Entity\CustomFieldBase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * migrate custom field values to regular model properties
 *
 */
class CustomFieldMigrateCommand extends ContainerAwareCommand
{
    protected $configReader;
    protected $config;
    protected $propertyAccessor;

    public function __construct(ConfigReader $configReader, array $config)
    {
        $this->configReader = $configReader;
        $this->config = $config;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('cube:customfield:migrate')
            ->setDescription('migrate custom field values to regular model properties')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        // select a custom field
        $classChoices = [];
        foreach ($this->config as $class => $classConfig) {
            $classChoices[] = $class;
        }

        $classQuestion = new ChoiceQuestion(
            'Which custom field would you like to migrate? (Select class)',
            $classChoices,
            0
        );
        $classQuestion->setErrorMessage('choice %s is invalid.');

        $class = $this->getHelper('question')->ask($input, $output, $classQuestion);

        $sourcePropertyChoices = [];
        foreach ($this->config[$class] as $sourceProperty => $config) {
            $sourcePropertyChoices[] = $sourceProperty;
        }

        $sourcePropertyQuestion = new ChoiceQuestion(
            'Which custom field would you like to migrate? (Select property)',
            $sourcePropertyChoices,
            0
        );
        $sourcePropertyQuestion->setErrorMessage('choice %s is invalid.');

        $sourceProperty = $this->getHelper('question')->ask($input, $output, $sourcePropertyQuestion);

        $config = $this->config[$class][$sourceProperty];

        // select a model property
        $classMetaData = $em->getMetadataFactory()->getMetadataFor($class);

        $targetPropertyChoices = [];
        foreach ($classMetaData->fieldMappings as $fieldMapping) {
            $targetPropertyChoices[] = $fieldMapping['fieldName'];
        }

        $targetPropertyQuestion = new ChoiceQuestion(
            'To which property would you like to move the data? (Select property)',
            $targetPropertyChoices,
            0
        );
        $targetPropertyQuestion->setErrorMessage('choice %s is invalid.');

        $targetProperty = $this->getHelper('question')->ask($input, $output, $targetPropertyQuestion);

        // define a value map
        $query = $em->createQuery('SELECT DISTINCT c.strRepresentation FROM '.CustomFieldBase::class.' c WHERE c.fieldId=:field_id');
        $query->setParameter('field_id', $sourceProperty);
        $values = $query->getResult();

        $createMapQuestion = new ConfirmationQuestion('There are '.count($values).' values. Would you like to create a map? (y/n)', true);

        if ($this->getHelper('question')->ask($input, $output, $createMapQuestion)) {
            $valueMap = [];

            foreach ($values as $key => $value) {
                $mapQuestion = new Question($value['strRepresentation'].': ');
                $valueMap[trim($value['strRepresentation'])] = $this->getHelper('question')->ask($input, $output, $mapQuestion);
            }

        } else {
            $valueMap = null;
        }

        // get busy
        foreach ($em->getRepository($class)->findAll() as $instance) {
            $source = trim($this->propertyAccessor->getValue($instance, $sourceProperty));

            if ($source) {
                if ($valueMap) {
                    $source = $valueMap[$source];
                }

                $this->propertyAccessor->setValue($instance, $targetProperty, $source);
                $em->persist($instance);
            }
        }

        $em->flush();
    }
}
