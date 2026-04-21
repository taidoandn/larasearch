type DropdownFacetStateParams = {
    currentValue: string[];
    optionValue: string;
    nextChecked: boolean | 'indeterminate';
    searchValue: string;
    searchable: boolean;
};

type DropdownFacetStateResult = {
    nextValue: string[];
    nextSearchValue: string;
};

export function getNextDropdownFacetState({
    currentValue,
    optionValue,
    nextChecked,
    searchValue,
    searchable,
}: DropdownFacetStateParams): DropdownFacetStateResult {
    return {
        nextValue:
            nextChecked === true
                ? Array.from(new Set([...currentValue, optionValue]))
                : currentValue.filter((item) => item !== optionValue),
        nextSearchValue: searchable ? '' : searchValue,
    };
}
