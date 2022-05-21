using DataCenter.Data.Enums;

namespace DataCenter.Data.Dtos
{
    public class PaymentMethodDto
    {
        public string Name { get; set; }

        public PaymentMethod Method { get; set; }
    }
}
