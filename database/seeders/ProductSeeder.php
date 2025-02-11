<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        Product::create([
            'name' => 'Пепперони',
            'description' => 'Пицца с пепперони и сыром',
            'price' => 799,
            'image' => 'pepperoni.jpg',
            'category' => 'Пицца',
        ]);

        Product::create([
            'name' => 'Гавайская',
            'description' => 'Пицца с ветчиной и ананасами',
            'price' => 899,
            'image' => 'hawaiian.jpg',
            'category' => 'Пицца',
        ]);

        Product::create([
            'name' => 'Маргарита',
            'description' => 'Пицца с томатным соусом, моцареллой и свежими листьями базилика',
            'price' => 699,
            'image' => 'margarita.jpg',
            'category' => 'Пицца',
        ]);

        Product::create([
            'name' => 'Четыре сыра',
            'description' => 'Пицца с моцареллой, горгонзолой, пармезаном и эмменталем',
            'price' => 899,
            'image' => 'four_cheeses.jpg',
            'category' => 'Пицца',
        ]);

        Product::create([
            'name' => 'Вегетарианская',
            'description' => 'Пицца с томатами, болгарским перцем, луком, грибами и оливками',
            'price' => 799,
            'image' => 'vegetarian.jpg',
            'category' => 'Пицца',
        ]);

        Product::create([
            'name' => 'Кола',
            'description' => 'Газированный напиток',
            'price' => 199,
            'image' => 'cola.jpg',
            'category' => 'Напитки',
        ]);

        Product::create([
            'name' => 'Чай',
            'description' => 'Вкусный, горячий, восточный',
            'price' => 100,
            'image' => 'tea.jpg',
            'category' => 'Напитки',
        ]);

        Product::create([
            'name' => 'Минеральная вода',
            'description' => 'Очищенная минеральная вода без газа',
            'price' => 99,
            'image' => 'mineral_water.jpg',
            'category' => 'Напитки',
        ]);

        Product::create([
            'name' => 'Мороженое',
            'description' => 'Пломбир с шоколадной крошкой и карамельным топпингом',
            'price' => 299,
            'image' => 'ice_cream.jpg',
            'category' => 'Десерты',
        ]);
    }
}
