<?php

class OscCartRetriever {
  public function retrieve() {
    
    if(!isset($_COOKIE['osCsid'])) {
      return NULL;
    }
    
    $mysqli = new mysqli($GLOBALS['OSC_CART_RETRIEVER_HOST'], $GLOBALS['OSC_CART_RETRIEVER_USER'], $GLOBALS['OSC_CART_RETRIEVER_PASS'], $GLOBALS['OSC_CART_RETRIEVER_DB']);
    $sessSql = "SELECT * FROM Sessions WHERE sesskey = ?";
    $cart = NULL;
    
    if ($stmt = $mysqli->prepare($sessSql)) {

        $stmt->bind_param("s", $_COOKIE['osCsid']);

        $stmt->execute();
        $stmt->bind_result($key, $expiry, $val);

            /* fetch values */
          while ($stmt->fetch()) {
              preg_match('/cart\|(.*)language\|/', $val, $matches);
              $cartSer = $matches[1];
              $oscCart = unserialize($cartSer);
              $cart = new oscCart($oscCart);
            }

        $stmt->fetch();
        $stmt->close();
    }

    $mysqli->close();
    
    return $cart;
  }
}

class shoppingCart {
   var $contents;
}

class oscCart {
  public $items = array();
  public $total = 0;
  public $weight = 0;
  public $totalItems = 0;
  
  function __construct($oscCart) {
    $this->total = $oscCart->total;
    $this->weight = $oscCart->weight;
    foreach($oscCart->contents as $k => $v) {
      $id = $k;
      $qty = $v['qty'];
      $this->totalItems += $qty;
      array_push($this->items, new oscCartItem($id, $qty));
    }
  }
}

class oscCartItem {
  public $name = "";
  public $quantity;
  public $url = "";
  public $id;
  
  function __construct($_id, $_qty) {
    $mysqli = new mysqli($GLOBALS['OSC_CART_RETRIEVER_HOST'], $GLOBALS['OSC_CART_RETRIEVER_USER'], $GLOBALS['OSC_CART_RETRIEVER_PASS'], $GLOBALS['OSC_CART_RETRIEVER_DB']);
    
    preg_match('/(\d+)\{?/', $_id, $matches);
    
    $productId = $matches[1];

    if ($stmt = $mysqli->prepare("SELECT products_name FROM products_description WHERE products_id = ?")) {

        $stmt->bind_param("s", $productId);

        $stmt->execute();
        $stmt->bind_result($productName);
        $stmt->fetch();
        $stmt->close();
    } else {
      echo $mysqli->error;
    }
    $mysqli->close();
    $this->name = $productName;
    $this->quantity = $_qty;
    $this->id = $_id;
    $this->url = "/product_info.php?products_id=$_id";
  }
}
?>