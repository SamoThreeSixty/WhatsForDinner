export interface Tag {
    id: number;
    slug: string;
    name: string;
}

export interface TagOption {
    label: string;
    value: string;
}

export function toTagOption(tag: Tag): TagOption {
    return {
        label: tag.name,
        value: tag.slug,
    };
}

export function toTagOptions(tags: Tag[]): TagOption[] {
    return Array.isArray(tags) ? tags.map(toTagOption) : [];
}
