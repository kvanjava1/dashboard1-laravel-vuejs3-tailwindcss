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
        createdAt: '2024-02-05'
    },
    {
        id: 5,
        title: 'Wildlife Encounters',
        description: 'Capturing the beauty and diversity of wildlife in their natural habitats.',
        coverImage: 'https://picsum.photos/seed/wildlife1/400/300',
        category: 'Wildlife',
        itemCount: 28,
        status: 'active',
        createdAt: '2024-02-10'
    },
    {
        id: 6,
        title: 'Art & Creativity',
        description: 'Abstract and contemporary art pieces showcasing human creativity and imagination.',
        coverImage: 'https://picsum.photos/seed/art1/400/300',
        category: 'Art',
        itemCount: 15,
        status: 'active',
        createdAt: '2024-02-15'
    },
    {
        id: 7,
        title: 'Product Photography',
        description: 'Professional product photography for e-commerce and marketing purposes.',
        coverImage: 'https://picsum.photos/seed/product1/400/300',
        category: 'Products',
        itemCount: 22,
        status: 'active',
        createdAt: '2024-02-20'
    },
    {
        id: 8,
        title: 'Event Coverage',
        description: 'Documenting special events, celebrations, and memorable occasions.',
        coverImage: 'https://picsum.photos/seed/event1/400/300',
        category: 'Events',
        itemCount: 35,
        status: 'active',
        createdAt: '2024-02-25'
    }
];