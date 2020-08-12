<?php
/**
 * 验证器参数解析
 * @author polaris
 */

namespace Jcsp\Core\Annotation\Parser;

use Jcsp\Core\Annotation\Mapping\Parameters;
use Swoft\Annotation\Annotation\Mapping\AnnotationParser;
use Swoft\Annotation\Annotation\Parser\Parser;
use Swoft\Annotation\Exception\AnnotationException;
use Swoft\Validator\ValidateRegister;

/**
 * Class ParametersParse
 *
 * @AnnotationParser(Parameters::class)
 *
 * @package Jcsp\Core\Annotation\Parser
 */
class ParametersParse extends Parser
{
    /**
     * parse
     *
     * @param int $type
     * @param Parameters $annotationObject
     *
     * @return array
     * @throws AnnotationException
     */
    public function parse(int $type, $annotationObject): array
    {
        $parameter = $annotationObject->getParameter();
        $fields = $annotationObject->getFields();
        $params = $annotationObject->getParams();
        $type = $annotationObject->getType();
        $unfields = $annotationObject->getUnfields();

//        var_dump($parameter);

        ValidateRegister::registerValidate(
            $this->className,
            $this->methodName,
            $parameter,
            $fields,
            $unfields,
            $params,
            '',
            $type
        );

        return [];
    }
}
