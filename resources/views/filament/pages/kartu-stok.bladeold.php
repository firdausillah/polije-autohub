<div class="w-full">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
                <th scope="col" class="px-4 py-3 text-sm font-medium tracking-wider text-left text-gray-500 uppercase">
                    Column 1
                </th>
                <th scope="col" class="px-4 py-3 text-sm font-medium tracking-wider text-left text-gray-500 uppercase">
                    Column 2
                </th>
                <th scope="col" class="px-4 py-3 text-sm font-medium tracking-wider text-left text-gray-500 uppercase">
                    Date
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
            @foreach ($data as $item)
            <tr>
                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $item['id'] }}</td>
                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $item['kode'] }}</td>
                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $item['name'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>