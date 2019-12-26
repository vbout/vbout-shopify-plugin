# vbout-Shopify-plugin
Shopify Plugin that link Metadata of orders, carts ,customers , products and with Integration settings.

## The Plugin has the Following Features :

  - Abandoned Cart Data
  - Search Data 
  - Registering a new customer data
  - Adding a new Product Data ( With variations, Category , price and images , descriptions)
  - Product Visits Data
  - Product Category Visit Data
  - Syncing Customers ( For customer data prior the use of the plugin) 
  - Syncing Product   ( For Product data prior the use of the plugin)
  ## limitations : 
    1 - We can't track user Login 
    2- We can't track user's behaviour ( search , category or product visit )
    2 - We can't uninstall the program from VBOUT, since the webhook responsible for the call of the Uninstallation of App, is constantly being called.
    4 - WE can add a tracker JS to compliment the limitations we have on Shopify Thrid Party Application, where we can track user's search, product and category visit.
  
## Variations : 
  
 Variations in Shopify are handled as new products, since Shopify automatically sends them as new product for every variation. 
 
## Search : 
  
  There is a no webhook for hooks activity for this we added a hook for every page load.
  
## Orders and Abandonded Carts : 
  
  ### Checkout : 
    
        There is a hook for checkout and does the following  since Shopify doesn't allow you to checkout without being registered and logged in first.
        It acts like create cart + create order in the same function.

  ### Create and Update Cart  : 
          there is a hook for both Cart Update and Cart create and they have the following functionalities : 
            - Create a new cart
            - Products are added ( a loop to handle them ) 

  ### Cart Item Remove : 

        Cart Update is handeled to remove all previous carts and adding all what is in cart (Remove from cart is acting like checkout update)

  ### Orders Create and Update : 
      The both have different hooks, they work the same. An Order is added with Shipping and Biling information, alongside with customer's information.
      
      - Updating Cart : 
          - In the process of updating cart, any update to status( Cancelled, Pending, Paid, Shipped/success), details, products is updated directly.

## Customers Add, Update and Sync :
    - We cannot know customers detail based on the log-in. Limitation from Shopify.
    - Customer's sync adds all the Customers that already bought from Shopify Store.

## Product Add Update and Sync :
      products are handeled differently in 
      . Every product having a variation, this variation can have different ( considered an independed product but under a parent product ) : 
      - Descritpion
      - Product id
      - Image
      - quantity 
      - price
      - sale price 

      For this the product Price and sale price will be zero only for parent products with Variations, this is because once you create a variation, you are not allowed to put products price, quantity and sale price.

    - Products are added , updated on an Admin page.
    - Product's sync adds all products that are in the system that were added and are still in stock.
    
## IP and Customer Link: 
    -We cannot register any kind any clicks , since it's Shopify based on limited webhooks , so we can't monitor user's search , IP upon login. 
    - WE can only have record of IP on Checkout and Order.
