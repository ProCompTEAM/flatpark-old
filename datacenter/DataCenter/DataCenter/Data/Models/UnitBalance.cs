using System.ComponentModel.DataAnnotations;
using DataCenter.Common;
using DataCenter.Data.Attributes;
using DataCenter.Data.Base;

namespace DataCenter.Data.Models
{
    public class UnitBalance : BaseEntity, IUnited
    {
        [Required, Unicode(Defaults.DefaultStringLength)]
        public string UnitId { get; set; }

        [Required]
        public double Balance { get; set; }
    }
}