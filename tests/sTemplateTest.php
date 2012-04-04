<?php
// require './00-global.php';
//
// class aTemplateVariableSetter implements sTemplateVariableSetter {
//   public static function getVariables($a) {
//     return array();
//   }
// }
//
// class sTemplateTest extends PHPUnit_Framework_TestCase {
//   /**
//    * @expectedException fValidationException
//    */
//   public function testBadTemplatePath() {
//     sTemplate::setTemplatesPath('non-existant');
//   }
//
//   public function setUp() {
//     sTemplate::setTemplatesPath('./template');
//   }
//
//   public function testAddJSFile() {
//     sTemplate::addJavaScriptFile('myfile.js');
//     $this->assertEquals(array('myfile.js'), sTemplate::getJavaScriptFiles());
//
//     sTemplate::addJavaScriptFile('/myfile2.js');
//     $this->assertEquals(array('myfile.js', 'myfile2.js'), sTemplate::getJavaScriptFiles());
//
//     sTemplate::addJavaScriptFile('./myfile3.js');
//     $this->assertEquals(array('myfile.js', 'myfile2.js', 'myfile3.js'), sTemplate::getJavaScriptFiles());
//
//     sTemplate::addJavaScriptFile('./myfile4.js', TRUE);
//     $this->assertEquals(array('myfile4.js', 'myfile.js', 'myfile2.js', 'myfile3.js'), sTemplate::getJavaScriptFiles());
//   }
//
//   /**
//    * @expectedException fProgrammerException
//    */
//   public function testSetActiveTemplateBadTemplate() {
//     sTemplate::setActiveTemplate('non-existant');
//   }
//
//   /**
//    * @expectedException fProgrammerException
//    */
//   public function testBufferBadFilename() {
//     sTemplate::buffer('non-existant');
//   }
//
//   public function testGetStylesheetsFromJSONFileDefault() {
//     $this->assertEquals('', sTemplate::getStylesheetsFromJSONFile());
//   }
//
//   public function testGetHeadJavaScriptFromJSONFileDefault() {
//     $this->assertEquals('', sTemplate::getHeadJavaScriptFromJSONFile());
//   }
//
//   public function testGetConditionalHeadJavaScriptFromJSONFileDefault() {
//     $this->assertEquals('', sTemplate::getConditionalHeadJavaScriptFromJSONFile());
//   }
//
//   public function testAddCDN() {
//     sTemplate::addCDN('http://myhost');
//     $this->assertEquals(array('http://myhost'), sTemplate::getCDNs());
//   }
//
//   /**
//    * @depends testAddCDN
//    */
//   public function testRemoveCDNs() {
//     sTemplate::removeCDNs();
//     $this->assertEquals(array(), sTemplate::getCDNs());
//   }
//
//   public function testAddBodyClassJustArray() {
//     sTemplate::addBodyClass('special');
//     $this->assertEquals(array('special'), sTemplate::getBodyClasses());
//   }
// }
