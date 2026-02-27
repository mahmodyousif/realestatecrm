@props(['customers']) 

<table class="premium-table">
    <thead>
        <tr>
            <th>#</th>
            <th>اسم العميل</th>
            <th>رقم الهوية</th>
            <th>رقم الجوال</th>
            <th>البريد الإلكتروني</th>
            <th>رقم الحساب</th>
            <th>الإجراء</th>
        </tr>
    </thead>
    <tbody>
        @foreach($customers as $customer)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $customer->name }}</td>
            <td>{{ $customer->id_card ?? '-' }}</td>
            <td>{{ $customer->phone ?? '-' }}</td>
            <td class="text-truncate" title="{{ $customer->email }}">{{ $customer->email ?? 'غير متوفر' }}</td>
            <td>{{ $customer->iban ?? '-' }}</td>
            <td>
                <div class="unit-actions-nested">
                    <a href="{{ $customer->type === 'marketer' 
                        ? route('marketer.show', $customer) 
                        : route('customer.show', $customer) }}"
                        class="action-link view" 
                        title="عرض">
                        <i class="fas fa-eye"></i>
                    </a>
                    
                    
                    <a href="{{ route('customer.edit', $customer) }}" class="action-link edit" title="تعديل"><i class="fas fa-edit"></i></a>
                    <form action="{{ route('customer.destroy', $customer) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="action-link delete"
                                title="حذف"
                                onclick="return confirm('هل أنت متأكد من حذف هذا العميل؟ لا يمكن التراجع عن العملية');">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>