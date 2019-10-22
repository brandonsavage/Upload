#include <stdio.h>
int main()
{
	float amount, result;
	int choice;
	printf("ENTER AMOUNT\n");
    scanf("%f",& amount);
    printf("1. prk to dollar\n");
    printf("2.dollar to pkr\n");
    printf("enter choice\n");
    scanf("%d",&choice);
    if(choice==1)
{
	result=amount/156;
}
else if(choice==2)
{
	result=amount*156;
}
printf("result=%f",result);

return 0;
}
