export type Category = {
    id: number
    parent_id: number | null
    name: string
    slug: string
    description?: string
    is_active: boolean
    created_at: string
    updated_at: string
}

const nowIso = () => new Date().toISOString()

const seed: Array<Omit<Category, 'created_at' | 'updated_at'>> = [
    { id: 1, parent_id: null, name: 'Announcements', slug: 'announcements', description: 'Important updates for all users.', is_active: true },
    { id: 2, parent_id: null, name: 'Tutorials', slug: 'tutorials', description: 'Guides and tutorials for onboarding.', is_active: true },
    { id: 3, parent_id: null, name: 'Events', slug: 'events', description: 'Company and community events.', is_active: false },
    { id: 4, parent_id: null, name: 'News', slug: 'news', description: 'Latest news and product updates.', is_active: true },
    { id: 5, parent_id: 4, name: 'Product News', slug: 'product-news', description: 'Product related announcements and updates.', is_active: true },
    { id: 6, parent_id: 4, name: 'Company News', slug: 'company-news', description: 'Company updates and internal highlights.', is_active: true },
    { id: 7, parent_id: 5, name: 'Releases', slug: 'releases', description: 'Release notes and changelogs.', is_active: true },
    { id: 8, parent_id: 3, name: 'Meetups', slug: 'meetups', description: 'Community meetups and gatherings.', is_active: true },
    { id: 9, parent_id: 3, name: 'Webinars', slug: 'webinars', description: 'Online sessions and webinars.', is_active: true },
    { id: 10, parent_id: 1, name: 'Internal', slug: 'internal', description: 'Internal-only announcements.', is_active: true }
]

export const makeDummyCategories = (): Category[] => {
    const now = nowIso()
    return seed.map((c) => ({ ...c, created_at: now, updated_at: now }))
}

