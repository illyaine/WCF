<?php
namespace wcf\system\form\builder\field;
use wcf\system\acl\simple\SimpleAclHandler;
use wcf\system\form\builder\field\data\CustomFormFieldDataProcessor;
use wcf\system\form\builder\IFormDocument;

/**
 * Implementation of a form field for setting simple acl.
 * 
 * Note: This form field should not be put in a simple `FormContainer` element
 * as its output already generates `.section` elements.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
class SimpleAclFormField extends AbstractFormField {
	/**
	 * @inheritDoc
	 */
	protected $templateName = 'aclSimple';
	
	/**
	 * @inheritDoc
	 */
	public function getHtmlVariables() {
		return [
			'__aclSimplePrefix' => $this->getPrefixedId(),
			'__aclInputName' => $this->getPrefixedId(),
			'aclValues' => SimpleAclHandler::getInstance()->getOutputValues($this->getValue() ?: [])
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasSaveValue() {
		return false;
	}
	
	
	/**
	 * @inheritDoc
	 */
	public function populate() {
		parent::populate();
		
		$this->getDocument()->getDataHandler()->add(new CustomFormFieldDataProcessor('i18n', function(IFormDocument $document, array $parameters) {
			if (is_array($this->getValue()) && !empty($this->getValue())) {
				$parameters[$this->getId()] = $this->getValue();
			}
			
			return $parameters;
		}));
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValue() {
		if (isset($_POST[$this->getPrefixedId()]) && is_array($_POST[$this->getPrefixedId()])) {
			$this->__value = $_POST[$this->getPrefixedId()];
		}
	}
}