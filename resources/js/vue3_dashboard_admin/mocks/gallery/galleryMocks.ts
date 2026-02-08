export interface GalleryMock {
    id: number;
    title: string;
    description: string;
    coverImage: string;
    category: string;
    itemCount: number;
    status: 'active' | 'inactive';
    createdAt: string;
}

export const galleryMocks: GalleryMock[] = [
    {
        id: 1,
        title: 'Nature Photography Collection',
        description: 'Beautiful landscapes and natural scenes captured around the world. From majestic mountains to serene beaches.',
        coverImage: 'https://picsum.photos/seed/nature1/400/300',
        category: 'Photography',
        itemCount: 24,
        status: 'active',
        createdAt: '2024-01-15'
    },
    {
        id: 2,
        title: 'Urban Architecture',
        description: 'Modern and classic architectural designs from city landscapes. Showcasing the beauty of urban structures.',
        coverImage: 'https://picsum.photos/seed/arch1/400/300',
        category: 'Architecture',
        itemCount: 18,
        status: 'active',
        createdAt: '2024-01-20'
    },
    {
        id: 3,
        title: 'Food & Cuisine Masterpieces',
        description: 'Delicious food photography showcasing culinary arts from around the globe.',
        coverImage: 'https://picsum.photos/seed/food1/400/300',
        category: 'Food',
        itemCount: 32,
        status: 'active',
        createdAt: '2024-02-01'
    },
    {
        id: 4,
        title: 'Travel Adventures 2024',
        description: 'Memorable moments from travel destinations worldwide. Capturing the essence of different cultures.',
        coverImage: 'https://picsum.photos/seed/travel1/400/300',
        category: 'Travel',
        itemCount: 45,
        status: 'active',
        createdAt: '2024-02-10'
    },
    {
        id: 5,
        title: 'Contemporary Art Collection',
        description: 'Contemporary and traditional art pieces from various artists. A curated selection of modern masterpieces.',
        coverImage: 'https://picsum.photos/seed/art1/400/300',
        category: 'Art',
        itemCount: 16,
        status: 'inactive',
        createdAt: '2024-01-05'
    },
    {
        id: 6,
        title: 'Wildlife in Natural Habitat',
        description: 'Amazing wildlife photography capturing animals in their natural habitat. Rare and beautiful moments.',
        coverImage: 'https://picsum.photos/seed/wild1/400/300',
        category: 'Wildlife',
        itemCount: 28,
        status: 'active',
        createdAt: '2024-02-15'
    },
    {
        id: 7,
        title: 'Corporate Events 2024',
        description: 'Professional coverage of corporate events, conferences, and business gatherings.',
        coverImage: 'https://picsum.photos/seed/event1/400/300',
        category: 'Events',
        itemCount: 52,
        status: 'active',
        createdAt: '2024-01-25'
    },
    {
        id: 8,
        title: 'Product Photography Studio',
        description: 'High-quality product shots for e-commerce and marketing purposes.',
        coverImage: 'https://picsum.photos/seed/product1/400/300',
        category: 'Products',
        itemCount: 38,
        status: 'active',
        createdAt: '2024-02-05'
    },
    {
        id: 9,
        title: 'Black & White Classics',
        description: 'Timeless black and white photography showcasing emotion and contrast.',
        coverImage: 'https://picsum.photos/seed/bw1/400/300',
        category: 'Photography',
        itemCount: 21,
        status: 'active',
        createdAt: '2024-01-30'
    },
    {
        id: 10,
        title: 'Street Food Adventures',
        description: 'Vibrant street food photography from markets around the world.',
        coverImage: 'https://picsum.photos/seed/street1/400/300',
        category: 'Food',
        itemCount: 29,
        status: 'inactive',
        createdAt: '2024-02-08'
    }
];
