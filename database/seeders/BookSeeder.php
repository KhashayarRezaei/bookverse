<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Book;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = [
            [
                'title' => 'The Great Gatsby',
                'author' => 'F. Scott Fitzgerald',
                'description' => 'A classic American novel about the Jazz Age, exploring themes of wealth, love, and the American Dream through the mysterious Jay Gatsby.',
                'price' => 12.99,
                'published_year' => 1925,
                'isbn' => '9780743273565',
            ],
            [
                'title' => 'To Kill a Mockingbird',
                'author' => 'Harper Lee',
                'description' => 'A powerful story of racial injustice and the loss of innocence in the American South, told through the eyes of young Scout Finch.',
                'price' => 14.99,
                'published_year' => 1960,
                'isbn' => '9780446310789',
            ],
            [
                'title' => '1984',
                'author' => 'George Orwell',
                'description' => 'A dystopian novel about totalitarianism, surveillance, and the manipulation of truth in a future society.',
                'price' => 11.99,
                'published_year' => 1949,
                'isbn' => '9780451524935',
            ],
            [
                'title' => 'Pride and Prejudice',
                'author' => 'Jane Austen',
                'description' => 'A romantic novel of manners that follows the emotional development of Elizabeth Bennet as she learns about the repercussions of hasty judgments.',
                'price' => 9.99,
                'published_year' => 1813,
                'isbn' => '9780141439518',
            ],
            [
                'title' => 'The Hobbit',
                'author' => 'J.R.R. Tolkien',
                'description' => 'A fantasy novel about Bilbo Baggins, a hobbit who embarks on an unexpected journey with thirteen dwarves to reclaim their homeland.',
                'price' => 15.99,
                'published_year' => 1937,
                'isbn' => '9780547928241',
            ],
            [
                'title' => 'The Catcher in the Rye',
                'author' => 'J.D. Salinger',
                'description' => 'A coming-of-age story about Holden Caulfield, a teenager struggling with alienation and loss in post-World War II America.',
                'price' => 13.99,
                'published_year' => 1951,
                'isbn' => '9780316769488',
            ],
            [
                'title' => 'Lord of the Flies',
                'author' => 'William Golding',
                'description' => 'A novel about a group of British boys stranded on an uninhabited island who descend into savagery, exploring human nature and civilization.',
                'price' => 10.99,
                'published_year' => 1954,
                'isbn' => '9780399501487',
            ],
            [
                'title' => 'Animal Farm',
                'author' => 'George Orwell',
                'description' => 'An allegorical novella about farm animals who rebel against their human farmer, only to find themselves under the rule of a new tyranny.',
                'price' => 8.99,
                'published_year' => 1945,
                'isbn' => '9780451526342',
            ],
            [
                'title' => 'The Alchemist',
                'author' => 'Paulo Coelho',
                'description' => 'A novel about a young Andalusian shepherd who dreams of finding a worldly treasure and embarks on a journey of self-discovery.',
                'price' => 16.99,
                'published_year' => 1988,
                'isbn' => '9780062315007',
            ],
            [
                'title' => 'The Kite Runner',
                'author' => 'Khaled Hosseini',
                'description' => 'A powerful story of friendship, betrayal, and redemption set against the backdrop of Afghanistan\'s turbulent history.',
                'price' => 17.99,
                'published_year' => 2003,
                'isbn' => '9781594631931',
            ],
            [
                'title' => 'The Book Thief',
                'author' => 'Markus Zusak',
                'description' => 'A novel set in Nazi Germany, narrated by Death, about a girl who steals books and shares them with neighbors during bombing raids.',
                'price' => 18.99,
                'published_year' => 2005,
                'isbn' => '9780375842207',
            ],
            [
                'title' => 'The Hunger Games',
                'author' => 'Suzanne Collins',
                'description' => 'A dystopian novel about Katniss Everdeen, who volunteers to take her sister\'s place in a televised battle to the death.',
                'price' => 14.99,
                'published_year' => 2008,
                'isbn' => '9780439023481',
            ],
            [
                'title' => 'The Fault in Our Stars',
                'author' => 'John Green',
                'description' => 'A young adult novel about two teenagers who meet at a cancer support group and fall in love, exploring themes of life, death, and love.',
                'price' => 13.99,
                'published_year' => 2012,
                'isbn' => '9780525478812',
            ],
            [
                'title' => 'Gone Girl',
                'author' => 'Gillian Flynn',
                'description' => 'A psychological thriller about a woman who disappears on her fifth wedding anniversary, and the media circus that follows.',
                'price' => 15.99,
                'published_year' => 2012,
                'isbn' => '9780307588364',
            ],
            [
                'title' => 'The Martian',
                'author' => 'Andy Weir',
                'description' => 'A science fiction novel about an astronaut who is stranded on Mars and must find a way to survive and return to Earth.',
                'price' => 16.99,
                'published_year' => 2011,
                'isbn' => '9780553418026',
            ],
            [
                'title' => 'All the Light We Cannot See',
                'author' => 'Anthony Doerr',
                'description' => 'A Pulitzer Prize-winning novel about a blind French girl and a German boy whose paths collide in occupied France during World War II.',
                'price' => 19.99,
                'published_year' => 2014,
                'isbn' => '9781476746586',
            ],
            [
                'title' => 'The Night Circus',
                'author' => 'Erin Morgenstern',
                'description' => 'A fantasy novel about a magical competition between two young illusionists, set in a mysterious circus that only opens at night.',
                'price' => 17.99,
                'published_year' => 2011,
                'isbn' => '9780307744432',
            ],
            [
                'title' => 'The Goldfinch',
                'author' => 'Donna Tartt',
                'description' => 'A Pulitzer Prize-winning novel about a boy who survives a terrorist attack at an art museum and becomes involved in the art underworld.',
                'price' => 20.99,
                'published_year' => 2013,
                'isbn' => '9780316055437',
            ],
            [
                'title' => 'Station Eleven',
                'author' => 'Emily St. John Mandel',
                'description' => 'A post-apocalyptic novel about a traveling Shakespeare company performing for survivors of a devastating flu pandemic.',
                'price' => 16.99,
                'published_year' => 2014,
                'isbn' => '9780804172448',
            ],
            [
                'title' => 'The Underground Railroad',
                'author' => 'Colson Whitehead',
                'description' => 'A Pulitzer Prize-winning novel that reimagines the Underground Railroad as an actual railroad, following a young slave\'s journey to freedom.',
                'price' => 18.99,
                'published_year' => 2016,
                'isbn' => '9780385542364',
            ],
        ];

        foreach ($books as $bookData) {
            Book::create($bookData);
        }

        $this->command->info('20 books have been seeded successfully!');
    }
}
