# Challenge
Hello, welcome to my project for this challenge it was fascinating and challenging to work on this project, eventually, I've come up with different solutions, so I will try to explain both of them below.
# Tools
- **Programming language** PHP 8.1
- **Framework** [Laravel 9](https://laravel.com/)
- **Database** [MariaDB 10.6](https://mariadb.org/)
- **Cache** [Redis](https://redis.io/)

# Table of contents
[Architecture](#Architecture)<br/>
[First approach (implemented)](#first-approach)
1. [ProductController](#ProductController)
2. [Discount calculation](#discount-calculation)
3. [Adding discounts](#adding-discounts)
4. [DB & Optimization techniques used](#db-and-optimization)
5. [Tests](#tests)

[Second approach](#second-approach)

[How to run the project?](#how-to-run-the-project)<br/>
[Permissions Issues](#permissions-issues)<br/>
[Persistent MySQL storage](#persistent-mysql-storage)
# Architecture
While I was working on the project, I came up with two solutions to the problem and chose the best one in terms of code beauty as well as system design. I'll go through both of them separately and explain my thoughts. The main difference between the two solutions is the different designs of the discount table and products.
# First approach
I think this approach is more sophisticated in terms of database design that's why I've chosen it. While solving this problem I tried to make the solution the fastest and most efficient, so as I describe my decisions, I will focus on performance. When designing any system I always try to find out how many daily users the system will have or more simply what the RPS value will be? This helps to understand how the system should be designed either well balanced or strongly optimized system. I would say that this particular approach is a well-balanced one but it still can be improved in terms of performance. Let's say that our system doesn't have so many daily users and imagine that RPS value is around 100-200 then the database will look like this
![alt text](https://i.ibb.co/ypfmSKM/tables.png)

The schema has such relation **Category**->**Products**->**Discounts**, a category has many products, a product belongs to a category and has many discounts, and a discount belongs to a product. Products table also has a combined index of category_id and original_price but I'll tell you about it a little bit later. I don't want to focus on that too much because I think this schema is simple and there is no need to explain it. The only thing I would like to tell you about is sku field, it's an integer field nothing fancy, but I would like to emphasize that I have created ```SkuCast``` for that, so whenever we get the data from the model it will add leading zeros to an integer for us. I think this database design is good in terms of best practices, it's balanced and straightforward, however, we can use different techniques to make it more efficient, I will tell you about one in [second approach](#second-approach). For now, I would like to start explaining how does current implementation of the project work? What does it consist of? Why would I use this technique over the other? By starting with the controller.

# ProductController
The endpoint ```/api/products``` is assigned to ```index()``` method in ProductController. I like to have skinny controllers, this one is no exception. Let's discuss what happens here. First, whenever ```/api/products``` is called request is validated, you'll receive an error if wrong data is passed, you can pass such fields: category(string), price(integer), operator(string), page(integer). The user can filter products by price and operator, the operator has 5 possible values: <,<=,>,>=,= and it's used in the query to DB. Then as you can see there are additional arguments: CategoryRepository, ProductRepository Laravel will create them from [Container](https://laravel.com/docs/9.x/container) for us. After resolving arguments, validation is being called ```$request->validated()```. Then if the category field is present, it finds category_id by a parameter passed from the request using CategoryRepository. I like to have an abstraction of the data layer, so in this case, [Repository pattern](https://medium.com/@pererikbergman/repository-design-pattern-e28c0f3e4a30) is useful and it's used across the project. In bigger projects, I also use interfaces for repositories and don't rely on *actual* repositories but for this project, I decided not to overload it with interfaces for repositories to keep it simple because the project doesn't have a huge codebase and just has a couple of repositories. After finding category_id we are almost good to go, the only thing that remains to be done is to create a ```ProductDTO``` object. I don't like to pass many parameters to methods, or even worse to pass an array with many parameters, I think in some cases it might be useful but I personally don't like it because when you pass an array of many parameters you lose control of the data that is being passed. That's why I use [DTO](https://en.wikipedia.org/wiki/Data_transfer_object) pattern here. It creates a ```ProductDTO``` from the array and then passes it to ProductRepository which will take care of filtering and paginating the data, so eventually ```$products``` variable will have a collection of 5 items(pagination by 5 items) and it's passed to ```ProductResource``` which is a representation of structure json response.

```php
/**
     * Index method, returns products by given filters
     *
     * @param ProductRequest     $request
     * @param CategoryRepository $categoryRepository
     * @param ProductRepository  $productRepository
     *
     * @return AnonymousResourceCollection
     */
    public function index(ProductRequest $request, CategoryRepository $categoryRepository, ProductRepository $productRepository): AnonymousResourceCollection
    {
        // Validate data
        $validated = $request->validated();
        $validated['category_id'] = isset($validated['category']) ? $categoryRepository->getByName(categoryName: $validated['category'])->id : null;

        // Create ProductDTO for querying products
        $productDTO = ProductDTO::createFromArrray(data: $validated);
        $products = $productRepository->products(productDTO: $productDTO);

        return ProductResource::collection($products);
    }
```

# Discount calculation
The discount calculation is implemented in Product model as two computed fields: final_price, discount_percentage, and the algorithm is pretty simple I think. It just takes all the discounts from the relation, finds the biggest one, and calculates the final_price.

```php
/**
     * Computes final_price
     *
     * @return float
     */
    public function getFinalPriceAttribute(): int
    {
        $discountPercentage = $this->calculateDiscountPercentage();

        // If the product doesn't have any discounts return the original price
        if ($discountPercentage === null) {
            return $this->original_price;
        }

        return $this->original_price * (100 - $discountPercentage) / 100;
    }

    /**
     * Computes discount_percentage
     *
     * @return string|null
     */
    public function getDiscountPercentageAttribute(): string|null
    {
        $discountPercentage = $this->calculateDiscountPercentage();

        return $discountPercentage ? $discountPercentage .'%' : null;
    }
    
    /**
     * Calculates discount percentage for current model
     *
     * @return int|null
     */
    private function calculateDiscountPercentage(): int|null
    {
        $discountPercentage = null;
        foreach ($this->discounts as $discount) {
            // If current $discount is more than previous one, assign $appliedDiscount to it
            $discountPercentage = ((int)$discountPercentage < $discount->percentage) ? $discount->percentage : $discountPercentage;
        }

        return $discountPercentage;
    }
```

There is also a little note here final_price is always rounded down. For example, if the original_price is 1011, discount_percentage is 30, then final_price would be 707 (1011*0.7=707.7). In real life, I think I would need to discuss rounding first.

# Adding discounts

I have created **DiscountService** for adding discounts to the products, you simply pass a collection of products, and then *addProductsDiscount* method creates discounts based on the values provided. I also want to mention that it uses chunk inserting, so instead of directly inserting a row it pushes new discounts to the array and when discounts are created it inserts them by 500 chunks which significantly increases the performance of inserts. However the main disadvantage of this database design (products has many relation to discounts) is that the system or the user should always keep track of already existing discounts which might be not easy when the project contains 20,000 products, that's the price we pay for the ability to have many discounts for a single product. I didn't add any restrictions for adding discounts for products, but I think a good way to fix this issue would be to add a limit of discounts that product can have, and if the limit is exceeded replace the old discount with the one. I also would make it possible for admins to keep track of discounts for every single product, so the admins could remove the ones that are outdated and etc.

```php
class DiscountService
{
    /**
     * Adds discount to products
     * This method can be used for different scenarios
     * It can add a discount either for a single product or for many products at the same time
     *
     * But make sure that the same discount doesn't exist yet
     *
     * @param Collection<Product> $products
     * @param int                 $percentage
     *
     * @return void
     */
    public function addProductsDiscount(Collection $products, int $percentage): void
    {
        $discounts = [];

        foreach ($products as $product) {
            $discounts[] = [
                'product_id' => $product->id,
                'percentage' => $percentage
            ];
        }

        // In order to make efficient queries split array by chunks and insert by chunks
        $discounts = array_chunk($discounts, 500);

        foreach ($discounts as $discount) {
            // Insert all new discounts by 500 rows, it's significantly boosts our performance
            Discount::query()->insert($discount);
        }
    }
}
```

# DB and Optimization
I think this database design is clean and good in terms of best practices of designing databases. I have implemented this in Laravel. I have created models: Category, Product, Discount and created an endpoint ```/api/products``` which makes takes items from ```products``` table. Let's see how fast and optimized our app by calling the endpoint.

![Response time](https://i.ibb.co/xX7YkdT/speed.png)
It took only 125ms to run this endpoint looks great, doesn't it? Well, the response time is really fast but let's also take a look at DB query log.

![Query log](https://i.ibb.co/SVb2CVg/queries.png)

It's not as good as we expected it to be. It took 11 queries to DB to get the data and that doesn't seem to be the best result, lets's see what we can do. The first thing that catches my eye is [N+1 problem](https://secure.phabricator.com/book/phabcontrib/article/n_plus_one/) in short, we have one select for ```products```, and then N additional selects for ```discounts``` and ```categories```, where N is the total number of products which is 5 in our case. We can fix this by [eager loading](https://www.tutorialspoint.com/entity_framework/entity_framework_eager_loading.htm#:~:text=Eager%20loading%20is%20the%20process,query%20results%20from%20the%20database.) ```discounts``` and ```categories```. After using eager loading only three queries will be executed - one to retrieve all of the products, one to retrieve all of the discounts for all of the products, and one to retrieve all of the categories for all of the products.

![Query log](https://i.ibb.co/4jg7LGY/queries-2.png)

I think it looks better, from this point I don't see any other *easy* ways to reduce the amount of queries, now it only depends on our database design. I think we can try to get rid of query to ```categories```. I assume that in order to filter products by category the frontend passes category name to our endpoint, so there are two ways to a avoid query for categories: 1. We can ask our frontend to preload categories somewhere, I think in LocalStorage and then pass category_id instead of name. 2. The second way is to cache ```categories```. Let's stick with the second one which I think is more interesting. What we can do is first find out how many categories we have now and how many will be added in the future? I think there won't be more than 300 and according to this amount we can conclude that we won't have any problems in the future and we can cache our ```categories```. For this purpose I will use this library [GeneaLabs/laravel-model-caching](https://github.com/GeneaLabs/laravel-model-caching) it allows to directly cache specific models in our case it's Category model, so we simply extend our model from CachedModel and set Redis as our main cache driver.

![Query log](https://i.ibb.co/3vJnFpJ/queries-3.png)

Wow! Now it takes only two queries to get products with discounts and category names, looks good so far. I think from this point we cannot reduce the amount of queries to DB because it depends on our database design, but what we can do is improve query performance. Let's run ```EXPLAIN``` on these two queries and look at the output.

![Explain query](https://i.ibb.co/GWn7WR3/full-scan.png)

What we can see is that for now in order to get the products the DB engine uses *Full Scan*. This means that in order to get the data it loops through every record in the database which is not efficient. From this point, we need to know which queries happen more often, for now, there are two types of queries(actually there are 4 of them but let's assume that there are 2 main queries): 1. Query with filter by category_id and price. 2. Query without category_id or price. The knowledge of the query frequency is important. But let's assume that the most popular type of query is the first one, then we need to create a combined index with respect to [Left-Prefix](https://orangematter.solarwinds.com/2019/02/05/the-left-prefix-index-rule/) rule so that the order will be: category_id, original_price. This index will help our DB engine to filter the data, now it won't loop through all table, instead, it will scan the index.

![Index query](https://i.ibb.co/vvyVBYB/index-condition.png)

Now we can see that DB engine went through one row, instead of six and found the data which indicates that index really boosts performance of this query. Lets get back to the second query.

![Explain query](https://i.ibb.co/HxB5bkn/second-query.png)

From this, we can see that since product_id is an index the DB engine uses the index to find the discounts, so we can't really optimize this query. That's it for this part of optimization. We can conclude that after all optimization steps we reduced the number of queries to DB from 11 to 2, and also improved the performance of the queries.

# Tests

For this project I decided to create Feature tests because I found them more useful. There are not many of them, but I think that they cover all the necessary functionality of the project. You can find them in ```src/tests/Feature``` directory. To run tests simply run this command
```console
docker-compose run artisan test
```

# Second approach

I also come up with the second approach for this challenge it's not much different from the first one but it can be as extension and can be done if the first approach is not enough, because it has this one has its own performance features. I haven't implemented it due to lack of time, but I think I can describe it here. So, lets imagine that this project is highload and many users often call ```/api/products``` endpoint then I think we need to redesign our DB. First, I always look for bottlenecks, look at log, look at statistics but in this case since I actually don't know anything I assume that current bottleneck is DB and we need to improve it. For now as you remember it takes two queries to DB to get the data for ```/api/products``` endpoint but we can reduce queries by one. In order to do that we will need to redesign products table. We have two queries, and I think we can get rid of selecting discounts, instead I will use [Denormalization technique](https://www.geeksforgeeks.org/denormalization-in-databases/). Products table will have two more columns: final_price, discount percentage. And since that, we don't need to query discounts table anymore because we don't need it, everything will be stored in products table. A main disadvantage of this approach is that we need to compute final_price and discount_percentage everytime we add a discount, but since it happens not that often I think it's not a big deal. We also won't be able to add multiple discounts to a product, instead our system will need to decide which discount to use and fill it in DB. Summarizing all of that we can conclude that according this approach and its pros and cons,  and also the fact that ```categories``` table is cached, our app will now make only one query to DB per request which is fantastic I think.
![Another way of designing DB](https://i.ibb.co/1KpmJ5k/db.png)

# How to run the project?

To get started, make sure you have [Docker installed](https://docs.docker.com/docker-for-mac/install/) on your system, and then clone this repository.

Next, navigate in your terminal to the directory you cloned this, and spin up the containers for the web server by running `docker-compose up -d --build site`.

Bringing up the Docker Compose network with `site` instead of just using `up`, ensures that only our site's containers are brought up at the start, instead of all of the command containers as well. The following are built for our web server, with their exposed ports detailed:

- **nginx** - `:80`
- **mysql** - `:3306`
- **php** - `:9000`
- **redis** - `:6379`
- **mailhog** - `:8025`

Use the following commands to init the project, but **make sure you are in src folder**.

```console
cd src
docker-compose run --rm composer install
docker-compose run artisan migrate:fresh --seed
```
Note that you need to run `docker-compose run artisan migrate:fresh --seed` **every time you start a container** because database is not consistent.

If you have any permission issues with storage folder and logs, simply run this command.
```console
sudo chmod -R ugo+rw storage
```

## Permissions Issues

If you encounter any issues with filesystem permissions while visiting your application or running a container command, try completing one of the sets of steps below.

**If you are using your server or local environment as the root user:**

- Bring any container(s) down with `docker-compose down`
- Rename `docker-compose.root.yml` file to `docker-compose.root.yml`, replacing the previous one
- Re-build the containers by running `docker-compose build --no-cache`

**If you are using your server or local environment as a user that is not root:**

- Bring any container(s) down with `docker-compose down`
- In your terminal, run `export UID=$(id -u)` and then `export GID=$(id -g)`
- If you see any errors about readonly variables from the above step, you can ignore them and continue
- Re-build the containers by running `docker-compose build --no-cache`

Then, either bring back up your container network or re-run the command you were trying before, and see if that fixes it.

## Persistent MySQL Storage

By default, whenever you bring down the Docker network, your MySQL data will be removed after the containers are destroyed. If you would like to have persistent data that remains after bringing containers down and back up, do the following:

1. Create a `mysql` folder in the project root, alongside the `nginx` and `src` folders.
2. Under the mysql service in your `docker-compose.yml` file, add the following lines:

```
volumes:
  - ./mysql:/var/lib/mysql
```
