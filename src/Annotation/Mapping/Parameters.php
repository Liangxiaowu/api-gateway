<?php declare(strict_types=1);


namespace Wudner\Gateway\Annotation\Mapping;

use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\Annotation\Target;
use Swoft\Validator\Annotation\Mapping\ValidateType;

/**
 * Class
 *
 * @since 2.0
 *
 * @Annotation
 * @Target("METHOD")
 * @Attributes({
 *     @Attribute("parameter", type="string"),
 *     @Attribute("fields", type="array"),
 *     @Attribute("params", type="array"),
 *     @Attribute("message", type="string"),
 * })
 */
class Parameters
{
    /**
     * @var string
     */
    private $parameter = '';

    /**
     * @var array
     */
    private $fields = [];

    /**
     * @var array
     */
    private $unfields = [];

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var string
     */
    private $type = ValidateType::BODY;

    /**
     * @var string
     */
    private $message = '';

    /**
     * Validate constructor.
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->parameter = $values['value'];
        } elseif (isset($values['parameter'])) {
            $this->parameter = $values['parameter'];
        }
        if (isset($values['fields'])) {
            $this->fields = $values['fields'];
        }
        if (isset($values['unfields'])) {
            $this->unfields = $values['unfields'];
        }
        if (isset($values['params'])) {
            $this->params = $values['params'];
        }
        if (isset($values['type'])) {
            $this->type = $values['type'];
        }
        if (isset($values['message'])) {
            $this->message = $values['message'];
        }
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getParameter(): string
    {
        return $this->parameter;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getUnfields(): array
    {
        return $this->unfields;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
