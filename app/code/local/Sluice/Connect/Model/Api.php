<?php
class Sluice_Connect_Model_Api extends Mage_Catalog_Model_Product_Api {
    public function parent($arg){
        if(empty($arg)){
            return array();
        }

        $result = array();
        foreach($arg as $productId){
            if(!is_numeric($productId)){
                continue;
            }

            $product = Mage::getModel('catalog/product')->load($productId);
            if(empty($product) || $product->getTypeId() != "simple"){
                $result[$productId] = null;
                continue;
            }

            $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
            if(isset($parentIds[0]) && !empty($parentIds[0])){
                $result[$productId] = $parentIds[0];
            } else {
                $result[$productId] = null;
            }
        }
        return $result;
    }

    public function childs($arg){
        if(empty($arg)){
            return array();
        }

        $result = array();
        foreach($arg as $productId){
            if(!is_numeric($productId)){
                continue;
            }

            $product = Mage::getModel('catalog/product')->load($productId);
            if(empty($product) || $product->getTypeId() != "configurable"){
                $result[$productId] = null;
                continue;
            }

            $childs = $product->getTypeInstance()->getUsedProducts();
            if(empty($childs)) {
                $result[$productId] = null;
                continue;
            }

            $result[$productId] = array();
            foreach($childs as $child){
                $result[$productId][] = $child->getId();
            }
        }
        return $result;
    }
}